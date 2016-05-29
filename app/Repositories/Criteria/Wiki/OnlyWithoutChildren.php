<?php

namespace Coyote\Repositories\Criteria\Wiki;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;

class OnlyWithoutChildren extends Criteria
{
    /**
     * @param \Coyote\Wiki $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->where('children', 0);
    }
}
