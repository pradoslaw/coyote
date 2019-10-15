<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Coupon;
use Coyote\Repositories\Contracts\CouponRepositoryInterface;

class CouponRepository extends Repository implements CouponRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Coupon::class;
    }

    /**
     * @inheritDoc
     */
    public function findCoupon(int $userId, float $amount): ?Coupon
    {
        return $this->where('user_id', $userId)->where('amount', '>=', $amount)->first();
    }
}
