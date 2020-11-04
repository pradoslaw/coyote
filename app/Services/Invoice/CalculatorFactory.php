<?php

namespace Coyote\Services\Invoice;

use Coyote\Payment;

class CalculatorFactory
{
    /**
     * @param Payment $payment
     * @return Calculator
     */
    public static function payment(Payment $payment): Calculator
    {
        return new Calculator([
            'price'         => $payment->plan->price,
            'discount'      => $payment->plan->discount,
            'vat_rate'      => self::vat($payment)
        ]);
    }

    private static function vat(Payment $payment): float
    {
        if (!empty($payment->job->firm->country_id)) {
            return $payment->job->firm->country->vat_rate;
        }

        return $payment->plan->vat_rate;
    }
}
