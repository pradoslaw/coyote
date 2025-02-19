<?php
namespace Coyote\Events;

use Coyote\Payment;
use Illuminate\Queue\SerializesModels;

class PaymentPaid
{
    use SerializesModels;

    public function __construct(public Payment $payment) {}
}
