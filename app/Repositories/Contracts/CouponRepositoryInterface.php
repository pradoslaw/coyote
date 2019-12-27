<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Coupon;

interface CouponRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $userId
     * @param float $amount
     * @return Coupon|null
     */
    public function findCoupon(int $userId, float $amount): ?Coupon;
}
