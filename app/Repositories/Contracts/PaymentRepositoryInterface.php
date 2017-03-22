<?php

namespace Coyote\Repositories\Contracts;

interface PaymentRepositoryInterface extends RepositoryInterface
{
    /**
     * Find any payments made by user within X days.
     *
     * @param int $userId
     * @param int $withIn
     * @return mixed
     */
    public function hasRecentlyPaid(int $userId, int $withIn = 7);
}
