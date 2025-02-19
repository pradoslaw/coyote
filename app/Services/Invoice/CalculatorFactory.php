<?php
namespace Coyote\Services\Invoice;

use Coyote\Payment;

class CalculatorFactory
{
    public static function payment(Payment $payment): Calculator
    {
        return new Calculator([
            'price'    => $payment->plan->price,
            'discount' => $payment->plan->discount,
            'vat_rate' => self::vat($payment),
        ]);
    }

    private static function vat(Payment $payment): float
    {
        $firm = $payment->job->firm;
        return new VatRateCalculator()->vatRate($firm?->country, $firm->vat_id);
    }
}
