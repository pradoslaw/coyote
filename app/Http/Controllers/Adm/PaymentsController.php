<?php
namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Events\PaymentPaid;
use Coyote\Http\Grids\Adm\PaymentsGrid;
use Coyote\Invoice;
use Coyote\Payment;
use Coyote\Repositories\Eloquent\PaymentRepository;
use Coyote\Services\Invoice\Pdf;
use Coyote\Services\UrlBuilder;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PaymentsController extends BaseController
{
    public function __construct(private PaymentRepository $payment)
    {
        parent::__construct();
    }

    public function index(): View
    {
        $this->breadcrumb->push('Faktury i płatności', route('adm.payments'));

        $grid = $this->gridBuilder()
            ->createGrid(PaymentsGrid::class)
            ->setSource(new EloquentSource($this->payment->filter()));

        return $this->view('adm.payments.home')->with('grid', $grid);
    }

    public function show(Payment $payment): View
    {
        $this->breadcrumb->push('Szczegóły płatności', route('adm.payments.show', ['payment' => $payment]));

        // load coupons even if they are deleted
        $payment->load(['coupon' => fn($query) => $query->withTrashed()]);

        return $this->view('adm.payments.show')->with([
            'payment'      => $payment,
            'payment_list' => Payment::getPaymentStatusesList(),
        ]);
    }

    /**
     * @param Payment $payment
     * @param Pdf $pdf
     * @return ResponseFactory|Response
     */
    public function invoice(Payment $payment, Pdf $pdf)
    {
        return response($pdf->create($payment), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->getFilename($payment->invoice) . '"',
        ]);
    }

    public function paid(Payment $payment): RedirectResponse
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

    private function getFilename(Invoice $invoice): string
    {
        return str_replace('/', '_', $invoice->number) . '.pdf';
    }
}
