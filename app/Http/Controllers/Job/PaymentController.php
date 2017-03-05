<?php

namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Events\PaymentPaid;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Job\PaymentForm;
use Coyote\Payment;
use Coyote\Repositories\Contracts\CurrencyRepositoryInterface as CurrencyRepository;
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
            'form'              => $this->createForm(PaymentForm::class, $payment),
            'net_price'         => $payment->netPrice(),
            'gross_price'       => $payment->grossPrice(),
            'vat'               => $payment->vat(),
            'exchange_rate'     => $this->currency->latest('EUR')
        ]);
    }

    /**
     * @param \Coyote\Payment $payment
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function makePayment($payment)
    {
        try {
            $payment->status_id = Payment::PAID;
            $payment->starts_at = Carbon::now();
            $payment->ends_at = Carbon::now()->addDays($payment->days);

            $payment->save();

            $payment->job->boost = true;
            $payment->job->save();

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
}
