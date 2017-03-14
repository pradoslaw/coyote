<?php

namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Events\PaymentPaid;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Job\PaymentForm;
use Coyote\Notifications\SuccessfulPaymentNotification;
use Coyote\Payment;
use Coyote\Repositories\Contracts\CurrencyRepositoryInterface as CurrencyRepository;
use Coyote\Services\Cardinity\Client as Cardinity;
use Coyote\Services\Cardinity\Exceptions\Declined;
use Coyote\Services\Cardinity\Exceptions\Forbidden;
use Coyote\Services\Cardinity\Exceptions\InternalServerError;
use Coyote\Services\Cardinity\Exceptions\ServiceUnavailable;
use Coyote\Services\Cardinity\Exceptions\ValidationFailed;
use Coyote\Services\Cardinity\Exceptions\Unauthorized;
use Coyote\Services\Cardinity\Payment\Create as PaymentCreate;
use Coyote\Services\Invoice\Generator as InvoiceGenerator;
use Coyote\Services\Invoice\Pdf as InvoicePdf;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * @var CurrencyRepository
     */
    private $currency;

    /**
     * @param CurrencyRepository $currency
     */
    public function __construct(CurrencyRepository $currency)
    {
        parent::__construct();

        $this->currency = $currency;

        $this->middleware(function (Request $request, $next) {
            /** @var \Coyote\Payment $payment */
            $payment = $request->route('payment');

            abort_if($payment->status_id == Payment::PAID, 404);

            return $next($request);
        });
    }

    /**
     * @param \Coyote\Payment $payment
     * @return \Illuminate\View\View
     */
    public function index($payment)
    {
        $this->breadcrumb->push('Praca', route('job.home'));
        $this->breadcrumb->push($payment->job->title, UrlBuilder::job($payment->job));
        $this->breadcrumb->push('Promowanie ogłoszenia');

        return $this->view('job.payment', [
            'form'              => $this->getForm($payment),
            'payment'           => $payment,
            'exchange_rate'     => $this->currency->latest('EUR')
        ]);
    }

    /**
     * @param \Coyote\Payment $payment
     * @param InvoiceGenerator $generator
     * @param InvoicePdf $pdf
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function process($payment, InvoiceGenerator $generator, InvoicePdf $pdf)
    {
        $form = $this->getForm($payment);
        $form->validate();

        try {
            $this->beginTransaction();

            /** @var \Coyote\Invoice $invoice */
            $invoice = $generator->create(
                array_merge($form->all()['invoice'], ['user_id' => $this->userId]),
                $payment
            );

            $payment->status_id = Payment::PAID;

            // establish plan's finish date
            $payment->starts_at = Carbon::now();
            $payment->ends_at = Carbon::now()->addDays($payment->days);

            // associate invoice with payment
            $payment->invoice()->associate($invoice);
            $payment->save();

            // boost job offer so it's on the top of the list
            $payment->job->boost = true;
            $payment->job->deadline_at = max($payment->job->deadline_at, $payment->ends_at);
            $payment->job->save();

            $result = Cardinity::create()->createPayment([
                'amount'            => round($payment->grossPrice() * $this->currency->latest('EUR'), 2),
                'currency'          => 'EUR',
                'settle'            => true,
                'order_id'          => $payment->id,
                'country'           => 'PL',
                'payment_method'    => PaymentCreate::CARD,
                'payment_instrument'=> [
                    'pan'           => $form->get('number')->getValue(),
                    'exp_year'      => $form->get('exp_year')->getValue(),
                    'exp_month'     => $form->get('exp_month')->getValue(),
                    'cvc'           => $form->get('cvc')->getValue(),
                    'holder'        => $form->get('name')->getValue()
                ],
            ]);

            logger()->debug('Successfully payment', ['uuid' => $result->id]);

            $this->auth->notify(new SuccessfulPaymentNotification($payment, $pdf));

            // reindex job offer
            event(new PaymentPaid($payment->job));

            $this->commit();
        } catch (Forbidden $e) {
            return $this->handlePaymentException($e, 'payment.forbidden');
        } catch (InternalServerError $e) {
            return $this->handlePaymentException($e, 'payment.internal_server');
        } catch (ServiceUnavailable $e) {
            return $this->handlePaymentException($e, 'payment.service_unavailable');
        } catch (Unauthorized $e) {
            return $this->handlePaymentException($e, 'payment.unauthorized');
        } catch (ValidationFailed $e) {
            return $this->handlePaymentException($e, 'payment.validation');
        } catch (Declined $e) {
            return $this->handlePaymentException($e, 'payment.declined');
        } catch (\Exception $e) {
            $this->rollback();

            throw $e;
        }

        return redirect()
            ->to(UrlBuilder::job($payment->job))
            ->with('success', trans('payment.success'));
    }

    private function handlePaymentException($exception, $translationId)
    {
        $this->rollback();

        return back()->withInput()->with('error', trans($translationId));
    }

    public function callback()
    {
        //
    }

    /**
     * @param \Coyote\Payment $payment
     * @return \Coyote\Services\FormBuilder\Form
     */
    private function getForm($payment)
    {
        return $this->createForm(PaymentForm::class, $payment);
    }

    private function beginTransaction()
    {
        app(Connection::class)->beginTransaction();
    }

    private function commit()
    {
        app(Connection::class)->commit();
    }

    private function rollback()
    {
        app(Connection::class)->rollback();
    }
}
