<?php

namespace Coyote\Services\Invoice;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Coyote\Payment;
use Mpdf\Mpdf;

class Pdf
{
    /**
     * @var ViewFactory
     */
    protected $view;

    /**
     * @var array
     */
    protected $vendor;

    /**
     * @param ViewFactory $view
     */
    public function __construct(ViewFactory $view)
    {
        $this->view = $view;
    }

    /**
     * @param array $vendor
     * @return $this
     */
    public function setVendor(array $vendor)
    {
        $this->vendor = $vendor;

        return $this;
    }

    /**
     * @param Payment $payment
     * @return string
     */
    public function create(Payment $payment)
    {
        $mpdf = new Mpdf();
        $mpdf->WriteHTML($this->view($payment)->render());

        return $mpdf->Output('', 'S');
    }

    /**
     * @param Payment $payment
     * @return \Illuminate\Contracts\View\View
     */
    protected function view(Payment $payment)
    {
        return $this->view->make('components.invoice', [
            'invoice'           => $payment->invoice,
            'currency'          => $payment->invoice->currency->symbol,
            'vendor'            => $this->vendor
        ]);
    }
}
