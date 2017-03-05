<?php

namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Events\PaymentPaid;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Job\PaymentForm;
use Coyote\Payment;
use Coyote\Repositories\Contracts\CurrencyRepositoryInterface as CurrencyRepository;
use Coyote\Repositories\Contracts\InvoiceRepositoryInterface as InvoiceRepository;
use Coyote\Services\Invoice\Enumerator;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * @var CurrencyRepository
     */
    private $currency;

    /**
     * @var InvoiceRepository
     */
    private $invoice;

    /**
     * @param CurrencyRepository $currency
     * @param InvoiceRepository $invoice
     */
    public function __construct(CurrencyRepository $currency, InvoiceRepository $invoice)
    {
        parent::__construct();

        $this->currency = $currency;
        $this->invoice = $invoice;

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
            'net_price'         => $payment->netPrice(),
            'gross_price'       => $payment->grossPrice(),
            'vat'               => $payment->vat(),
            'exchange_rate'     => $this->currency->latest('EUR')
        ]);
    }

    /**
     * @param \Coyote\Payment $payment
     * @param Enumerator $enumerator
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function makePayment($payment, Enumerator $enumerator)
    {
        $form = $this->getForm($payment);
        $form->validate();

        try {
            $this->transaction(function () use ($payment, $form, $enumerator) {
                /** @var \Coyote\Invoice $invoice */
                $invoice = $this->invoice->newInstance(
                    array_merge($form->get('invoice')->getChildrenValues(), ['user_id' => $this->userId])
                );

                // setup invoice number
                $enumerator->setup($invoice);
                // save the invoice
                $invoice->save();

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
