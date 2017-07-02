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
            'vat_rate'      => $payment->plan->vat_rate
        ]);
    }
}
