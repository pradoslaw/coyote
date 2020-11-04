<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Events\PaymentPaid;
use Coyote\Exceptions\PaymentFailedException;
use Coyote\Firm;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\Job\PaymentRequest;
use Coyote\Payment;
use Coyote\Repositories\Contracts\CountryRepositoryInterface as CountryRepository;
use Coyote\Repositories\Contracts\CouponRepositoryInterface as CouponRepository;
use Coyote\Repositories\Contracts\PaymentRepositoryInterface as PaymentRepository;
use Coyote\Services\Invoice\CalculatorFactory;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\Services\Invoice\Generator as InvoiceGenerator;
use Illuminate\Database\Connection as Db;
use Illuminate\Http\Request;
use GuzzleHttp\Client as HttpClient;

class PaymentController extends Controller
{
    private const ERROR = 'error';

    /**
     * @var InvoiceGenerator
     */
    private $invoice;

    /**
     * @var CountryRepository
     */
    private $country;

    /**
     * @var Db
     */
    private $db;

    /**
     * @var CouponRepository
     */
    private $coupon;

    /**
     * @var array
     */
    private $vatRates;

    /**
     * @param InvoiceGenerator $invoice
     * @param Db $db
     * @param CountryRepository $country
     * @param CouponRepository $coupon
     */
    public function __construct(InvoiceGenerator $invoice, Db $db, CountryRepository $country, CouponRepository $coupon)
    {
        parent::__construct();

        $this->invoice = $invoice;
        $this->db = $db;
        $this->country = $country;
        $this->coupon = $coupon;

        $this->middleware(
            function (Request $request, $next) {
                /** @var \Coyote\Payment $payment */
                $payment = $request->route('payment');

                if ($payment !== null && $payment instanceof Payment) {
                    abort_if($payment->status_id == Payment::PAID, 404);
                }

                return $next($request);
            },
            ['except' => 'success']
        );

        $this->breadcrumb->push('Praca', route('job.home'));
        $this->vatRates = $this->country->vatRatesList();
    }

    /**
     * @param \Coyote\Payment $payment
     * @return \Illuminate\View\View
     */
    public function index(Payment $payment)
    {
        $this->breadcrumb->push($payment->job->title, UrlBuilder::job($payment->job));
        $this->breadcrumb->push('Płatność');

        $firm = $payment->job->firm ?? new Firm();

        if (empty($firm->country_id)) {
            $geoIp = app('geo-ip');
            $result = $geoIp->ip($this->request->ip());

            $firm->country = $result->country_code ?? '';
        }

        // calculate price based on payment details
        $calculator = CalculatorFactory::payment($payment);

        $countries = $this->country->pluck('code', 'id');

        return $this->view('job.payment', [
            'payment'           => $payment,
            'vat_rates'         => $this->vatRates,
            'calculator'        => $calculator->toArray(),
            'firm'              => $firm,
            'countries'         => $countries
        ]);
    }

    /**
     * @param PaymentRequest $request
     * @param Payment $payment
     * @return string
     */
    public function process(PaymentRequest $request, Payment $payment)
    {
        $calculator = CalculatorFactory::payment($payment);

        $coupon = $this->coupon->findBy('code', $request->input('coupon'));
        $calculator->setCoupon($coupon);

        $payment->coupon_id = $coupon->id ?? null;

        // begin db transaction
        $response = $this->handlePayment(function () use ($payment, $request, $calculator, $coupon) {
            // save invoice data. keep in mind that we do not setup invoice number until payment is done.
            /** @var \Coyote\Invoice $invoice */
            $invoice = $this->invoice->create(
                array_merge($request->input('enable_invoice') ? $request->input('invoice') : [], ['user_id' => $this->auth->id]),
                $payment,
                $calculator
            );

            // associate invoice with payment
            $payment->invoice()->associate($invoice);

            if ($payment->job->firm_id) {
                // update firm's VAT ID
                $payment->job->firm->vat_id = $request->input('invoice.vat_id');
                $payment->job->firm->save();
            }

            $payment->save();

            if (!$calculator->grossPrice()) {
                return $this->successfulTransaction($payment);
            }

            // make a payment
            return $this->{'make' . ucfirst($request->input('payment_method')) . 'Transaction'}($payment);
        });

        return $response->getTargetUrl();
    }

    /**
     * Successful bank transfer transaction. Redirect to the offer.
     *
     * @param Payment $payment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Payment $payment)
    {
        return redirect()
            ->to(UrlBuilder::job($payment->job))
            ->with('success', trans('payment.pending'));
    }

    /**
     * @param Request $request
     * @param HttpClient $client
     * @param PaymentRepository $payment
     * @throws \Exception
     */
    public function paymentStatus(Request $request, HttpClient $client, PaymentRepository $payment)
    {
        /** @var \Coyote\Payment $payment */
        $payment = $payment->findBy('session_id', $request->get('p24_session_id'));
        abort_if($payment === null, 404);

        $crc = md5(
            join(
                '|',
                array_merge(
                    $request->only(['p24_session_id', 'p24_order_id', 'p24_amount', 'p24_currency']),
                    [config('services.p24.salt')]
                )
            )
        );

        try {
            if ($request->get('p24_sign') !== $crc) {
                throw new \InvalidArgumentException(
                    sprintf('Crc does not match in payment: %s.', $payment->session_id)
                );
            }

            $response = $client->post(config('services.p24.verify_url'), [
                'form_params' => $request->except(['p24_method', 'p24_statement', 'p24_amount'])
                    + ['p24_amount' => round($payment->invoice->grossPrice() * 100)]
            ]);

            $body = \GuzzleHttp\Psr7\parse_query($response->getBody());

            if (!isset($body['error']) || $body['error'] != 0) {
                throw new \InvalidArgumentException(
                    sprintf('[%s]: %s', $payment->session_id, $response->getBody())
                );
            }

            event(new PaymentPaid($payment));
        } catch (\Exception $e) {
            logger()->debug($request->all());

            throw $e;
        }
    }

    /**
     * @param Request $request
     * @param PaymentRepository $payment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function payment3DSecure(Request $request, PaymentRepository $payment)
    {
        $salt        = config('services.paylane.salt');
        $status      = $request->input('status');
        $description = $request->input('description');
        $amount      = $request->input('amount');
        $currency    = $request->input('currency');
        $hash        = $request->input('hash');

        $id = '';
        if ($status !== self::ERROR) {
            $id = $request->input('id_3dsecure_auth');
        }

        $calcHash = sha1("{$salt}|{$status}|{$description}|{$amount}|{$currency}|{$id}");
        abort_if($calcHash !== $hash, 500, "Error, wrong hash");

        $payment = $payment->findOrFail($description);
        abort_if($payment === null, 404);

        $client = new \PayLaneRestClient(config('services.paylane.username'), config('services.paylane.password'));

        return $this->handlePayment(function () use ($client, $id, $payment) {
            $result = $client->saleBy3DSecureAuthorization(['id_3dsecure_auth' => $id]);

            if (!$result['success']) {
                $error = $result['error'];
                logger()->error(var_export($result, true));

                throw new PaymentFailedException(
                    trans('payment.validation', ['message' => $error['error_description']]),
                    $error['error_number']
                );
            }

            return $this->successfulTransaction($payment);
        });
    }

    /**
     * Card transaction was successful. Reindex and return to the offer
     *
     * @param Payment $payment
     * @return \Illuminate\Http\RedirectResponse
     */
    private function successfulTransaction(Payment $payment)
    {
        // boost job offer, send invoice and reindex
        event(new PaymentPaid($payment));

        return redirect()
            ->to(UrlBuilder::job($payment->job))
            ->with('success', trans('payment.success'));
    }

    /**
     * @param Payment $payment
     * @throws PaymentFailedException
     * @return \Illuminate\Http\RedirectResponse
     */
    private function makeCardTransaction(Payment $payment)
    {
        $client = new \PayLaneRestClient(config('services.paylane.username'), config('services.paylane.password'));

        list($month, $year) = explode('/', $this->request->input('exp'));

        $params = [
            'sale' => [
                'amount'            => number_format($payment->invoice->grossPrice(), 2, '.', ''),
                'currency'          => 'PLN',
                'description'       => $payment->id
            ],
            'customer' => [
                'name'              => $this->request->input('name'),
                'email'             => $this->auth->email,
                'ip'                => $this->request->ip()
            ],
            'card' => [
                'card_number'       => str_replace('-', '', $this->request->input('number')),
                'name_on_card'      => $this->request->input('name'),
                'expiration_month'  => sprintf('%02d', $month),
                'expiration_year'   => '20' . $year,
                'card_code'         => $this->request->input('cvc'),
            ],
            'back_url'              => route('job.payment.3dsecure')
        ];

        /** @var array $result */
        $result = $client->checkCard3DSecure($params);

        if (!$result['success']) {
            $error = $result['error'];
            logger()->error(var_export($result, true));

            throw new PaymentFailedException(
                trans('payment.validation', ['message' => $error['error_description']]),
                $error['error_number']
            );
        }

        if ($result['is_card_enrolled']) {
            return redirect()->to($result['redirect_url']);
        } else {
            $result = $client->saleBy3DSecureAuthorization(['id_3dsecure_auth' => $result['id_3dsecure_auth']]);
        }

        logger()->debug('Successfully payment', ['result' => $result]);

        return $this->successfulTransaction($payment);
    }

    /**
     * @param Payment $payment
     * @return \Illuminate\Http\RedirectResponse
     */
    private function makeTransferTransaction(Payment $payment)
    {
        $payment->session_id = str_random(90);
        $payment->save();

        return redirect()->route('job.gateway', [$payment]);
    }

    public function gateway(Payment $payment)
    {
        return $this->view('job.gateway', [
            'payment' => $payment
        ]);
    }

    /**
     * @param \Closure $callback
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    private function handlePayment(\Closure $callback)
    {
        $this->db->beginTransaction();

        try {
            $result = $callback();
            $this->db->commit();

            return $result;
        } catch (PaymentFailedException $e) {
            return $this->handlePaymentException($e);
        } catch (\Exception $e) {
            return $this->handlePaymentException($e);
        }
    }

    /**
     * Handle payment exception. Remove sensitive data before saving to logs and sending to sentry.
     *
     * @param \Exception $exception
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handlePaymentException($exception)
    {
        // remove sensitive data
        $this->request->merge(['number' => '***', 'cvc' => '***']);
        $_POST['number'] = $_POST['cvc'] = '***';

        $this->db->rollBack();
        // log error. sensitive data won't be saved (we removed them)
        logger()->error($exception);

        if (app()->environment('production')) {
            app('sentry')->captureException($exception);
        }

        throw $exception;
    }
}
