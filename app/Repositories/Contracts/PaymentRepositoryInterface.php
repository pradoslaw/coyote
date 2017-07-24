<?php

namespace Coyote\Repositories\Contracts;

interface PaymentRepositoryInterface extends RepositoryInterface
{
    /**
     * @return \Coyote\Payment[]
     */
    public function ongoingPaymentsWithBoostBenefit();

    /**
     * @return mixed
     */
    public function filter();
}
