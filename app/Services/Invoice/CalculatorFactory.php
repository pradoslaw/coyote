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
            'price'         => $payment->plan->discount > 0 ? $payment->plan->price * $payment->plan->discount : $payment->plan->price,
            'vat_rate'      => $payment->plan->vat_rate
        ]);
    }
}
