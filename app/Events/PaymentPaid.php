<?php

namespace Coyote\Events;

use Coyote\Payment;
use Illuminate\Queue\SerializesModels;

class PaymentPaid
{
    use SerializesModels;

    /**
     * @var Payment
     */
    public $payment;

    /**
     * @param Payment $payment
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }
}
