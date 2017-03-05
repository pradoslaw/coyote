<?php

namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Events\PaymentPaid;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Job\PaymentForm;
use Coyote\Notifications\SuccessfulPaymentNotification;
use Coyote\Payment;
use Coyote\Repositories\Contracts\CurrencyRepositoryInterface as CurrencyRepository;
use Coyote\Services\Invoice\Generator as InvoiceGenerator;
use Coyote\Services\Invoice\Pdf as InvoicePdf;
use Coyote\Services\UrlBuilder\UrlBuilder;
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
    public function makePayment($payment, InvoiceGenerator $generator, InvoicePdf $pdf)
    {
        $form = $this->getForm($payment);
        $form->validate();

        try {
            $this->transaction(function () use ($payment, $form, $generator, $pdf) {
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
                $payment->job->save();

                $pdf->setVendor(config('vendor'));

                $this->auth->notify(new SuccessfulPaymentNotification($payment, base64_encode($pdf->create($payment))));
            });

            // reindex job offer
            event(new PaymentPaid($payment->job));
        } catch (\Exception $e) {
            throw $e;
        }

        return redirect()
            ->to(UrlBuilder::job($payment->job))
            ->with('success', 'Płatność zaakceptowana. Rozpoczynamy promowanie ogłoszenia.');
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
}
