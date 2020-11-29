<?php

namespace Coyote\Repositories\Criteria\Microblog;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;

class OrderById extends Criteria
{
    /**
     * @var bool
     */
    protected $withPremium;

    /**
     * OrderById constructor.
     * @param bool $withPremium
     */
    public function __construct(bool $withPremium = true)
    {
        $this->withPremium = $withPremium;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($model, Repository $repository)
    {
        if ($this->withPremium) {
            $model = $model->orderBy('microblogs.is_sponsored', 'DESC');
        }

        return $model->orderBy('microblogs.id', 'DESC');
    }
}
