<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Feature;
use Coyote\Repositories\Contracts\FeatureRepositoryInterface;

class FeatureRepository extends Repository implements FeatureRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Feature::class;
    }
}
