<?php

namespace Coyote\Exceptions;

use Coyote\Payment;

class PaymentFailedException extends \Exception
{
    /**
     * @var Payment
     */
    public $payment;

    /**
     * @param Payment $payment
     * @return $this
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;

        return $this;
    }
}
