<?php

namespace Coyote\Events;

use Coyote\Payment;
use Coyote\User;
use Illuminate\Queue\SerializesModels;

class PaymentPaid
{
    use SerializesModels;

    /**
     * @var Payment
     */
    public $payment;

    /**
     * @var User
     */
    public $user;

    /**
     * @param Payment $payment
     * @param User $user
     */
    public function __construct(Payment $payment, User $user)
    {
        $this->payment = $payment;
        $this->user = $user;
    }
}
