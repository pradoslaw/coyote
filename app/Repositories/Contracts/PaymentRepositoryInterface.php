<?php

namespace Coyote\Repositories\Contracts;

interface PaymentRepositoryInterface extends RepositoryInterface
{
    /**
     * Find any payments made by user within X days.
     *
     * @param int $userId
     * @param int $days
     * @return mixed
     */
    public function hasRecentlyPaid(int $userId, int $days = 7);

    /**
     * @return \Coyote\Payment[]
     */
    public function ongoingPaymentsWithBoostBenefit();

    /**
     * @return mixed
     */
    public function filter();
}
