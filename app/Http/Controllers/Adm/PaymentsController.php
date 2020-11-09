<?php

namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Events\PaymentPaid;
use Coyote\Http\Grids\Adm\PaymentsGrid;
use Coyote\Payment;
use Coyote\Repositories\Contracts\PaymentRepositoryInterface as PaymentRepository;
use Coyote\Services\Invoice\Pdf;
use Coyote\Services\UrlBuilder;

class PaymentsController extends BaseController
{
    /**
     * @var PaymentRepository
     */
    private $payment;

    /**
     * @param PaymentRepository $payment
     */
    public function __construct(PaymentRepository $payment)
    {
        parent::__construct();

        $this->payment = $payment;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Faktury i płatności', route('adm.payments'));

        $grid = $this->gridBuilder()
            ->createGrid(PaymentsGrid::class)
            ->setSource(new EloquentSource($this->payment->filter()));

        return $this->view('adm.payments.home')->with('grid', $grid);
    }

    /**
     * @param Payment $payment
     * @return \Illuminate\View\View
     */
    public function show(Payment $payment)
    {
        $this->breadcrumb->push('Szczegóły płatności');

        // load coupons even if they are deleted
        $payment->load([
            'coupon' => function ($query) {
                return $query->withTrashed();
            }
        ]);

        return $this->view('adm.payments.show')->with([
            'payment'       => $payment,
            'payment_list'  => Payment::getPaymentStatusesList()
        ]);
    }

    /**
     * @param Payment $payment
     * @param Pdf $pdf
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function invoice(Payment $payment, Pdf $pdf)
    {
        return response($pdf->create($payment), 200, [
            'Content-Type'          => 'application/pdf',
            'Content-Disposition'   => 'attachment; filename="' . $this->getFilename($payment->invoice) . '"'
        ]);
    }

    /**
     * @param Payment $payment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function paid(Payment $payment)
    {
        if ($payment->status_id === Payment::PAID && $payment->invoice_id) {
            abort(404);
        }

        $this->transaction(function () use ($payment) {
            // boost job offer, send invoice and reindex
            event(new PaymentPaid($payment));
        });

        return redirect()
            ->to(UrlBuilder::job($payment->job))
            ->with('success', 'Płatność została ustawiona.');
    }

    /**
     * @param \Coyote\Invoice $invoice
     * @return string
     */
    private function getFilename($invoice): string
    {
        return str_replace('/', '_', $invoice->number) . '.pdf';
    }
}
