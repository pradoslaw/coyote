<?php

namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Http\Grids\Adm\PaymentsGrid;
use Coyote\Payment;
use Coyote\Repositories\Contracts\PaymentRepositoryInterface as PaymentRepository;
use Coyote\Services\Invoice\Pdf;

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

        return $this->view('adm.payments.show')->with([
            'payment'       => $payment,
            'payment_list'  => Payment::getPaymentsList()
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
     * @param \Coyote\Invoice $invoice
     * @return string
     */
    private function getFilename($invoice): string
    {
        return str_replace('/', '_', $invoice->number) . '.pdf';
    }
}
