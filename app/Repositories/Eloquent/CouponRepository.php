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
}
