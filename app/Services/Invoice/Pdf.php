<?php

namespace Coyote\Services\Invoice;

use Coyote\Payment;
use Mpdf\Mpdf;

class Pdf
{
    /**
     * @var array
     */
    protected $vendor;

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
        $mpdf = new Mpdf(['tempDir' => storage_path('app')]);
        $mpdf->WriteHTML($this->view($payment)->render());

        return $mpdf->Output('', 'S');
    }

    /**
     * @param Payment $payment
     * @return \Illuminate\Contracts\View\View
     */
    protected function view(Payment $payment)
    {
        return view('legacyComponents.invoice', [
            'invoice'           => $payment->invoice,
            'currency'          => $payment->invoice->currency->symbol,
            'vendor'            => $this->vendor
        ]);
    }
}
