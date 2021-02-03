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
use Coyote\Services\UrlBuilder;
use Coyote\Services\Invoice\Generator as InvoiceGenerator;
use Illuminate\Database\Connection as Db;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentController extends Controller
{
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
            'vat_rate'          => $calculator->vatRate,
            'net_price'         => $calculator->netPrice(),
            'firm'              => $firm,
            'countries'         => $countries,
            'stripe_key'        => config('services.stripe.key')
        ]);
    }

    /**
     * @param PaymentRequest $request
     * @param Payment $payment
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Stripe\Exception\ApiErrorException
     * @throws \Throwable
     */
    public function makePayment(PaymentRequest $request, Payment $payment)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $coupon = $this->coupon->findBy('code', $request->input('coupon'));
        $payment->coupon_id = $coupon->id ?? null;

        $calculator = CalculatorFactory::payment($payment);
        $calculator->setCoupon($coupon);

        $invoice = [];

        if ($request->input('enable_invoice')) {
            $calculator->setCountry($this->country->find($request->input('invoice.country_id')));
            $invoice = $request->input('invoice');

            if ($payment->job->firm_id) {
                // update firm's VAT ID
                $payment->job->firm->fill($request->only(['invoice.vat_id', 'invoice.country_id'])['invoice']);
                $payment->job->firm->save();
            }
        }

        $this->db->beginTransaction();

        try {
            // save invoice data. keep in mind that we do not setup invoice number until payment is done.
            /** @var \Coyote\Invoice $invoice */
            $invoice = $this->invoice->create(
                array_merge($invoice, ['user_id' => $this->auth->id]),
                $payment,
                $calculator
            );

            // associate invoice with payment
            $payment->invoice()->associate($invoice);

            $payment->save();
            $this->db->commit();
        } catch (PaymentFailedException $e) {
            $this->handlePaymentException($e);
        } catch (\Exception $e) {
            $this->handlePaymentException($e);
        }

        if (!$calculator->grossPrice()) {
            return $this->successfulTransaction($payment);
        }

        $intent = PaymentIntent::create([
            'amount'                => $payment->invoice->grossPrice() * 100,
            'currency'              => strtolower($payment->invoice->currency->name),
            'metadata'              => ['id' => $payment->id],
            'payment_method_types'  => [$request->input('payment_method')]
        ]);

        return [
            'token'             => $intent->client_secret,
            'success_url'       => route('job.payment.success', [$payment]),
            'status_url'        => route('job.payment.status', [$payment])
        ];
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
     * @param PaymentRepository $repository
     * @throws SignatureVerificationException
     */
    public function paymentStatus(PaymentRepository $repository)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = @file_get_contents('php://input');
        $event = null;

        try {
            $event = Webhook::constructEvent($payload, $_SERVER['HTTP_STRIPE_SIGNATURE'], config('services.stripe.endpoint_secret'));
        } catch (\UnexpectedValueException | SignatureVerificationException $e) {
            throw $e;
        }

        if ($event->type !== 'payment_intent.succeeded') {
            return;
        }

        $paymentIntent = $event->data->object;
        $payment = $repository->findOrFail($paymentIntent->metadata->id);

        event(new PaymentPaid($payment));
    }

    /**
     * @param Payment $payment
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    private function successfulTransaction(Payment $payment)
    {
        // boost job offer, send invoice and reindex
        event(new PaymentPaid($payment));

        session()->flash('success', trans('payment.success'));

        return response(UrlBuilder::job($payment->job, true), 201);
    }

    /**
     * Handle payment exception. Remove sensitive data before saving to logs and sending to sentry.
     *
     * @param \Exception $exception
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handlePaymentException($exception)
    {
        $this->db->rollBack();
        // log error. sensitive data won't be saved (we removed them)
        logger()->error($exception);

        if (app()->environment('production')) {
            app('sentry')->captureException($exception);
        }

        throw $exception;
    }
}
