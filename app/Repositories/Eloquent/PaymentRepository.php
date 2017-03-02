<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Payment;
use Coyote\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentRepository extends Repository implements PaymentRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Payment::class;
    }
}
