<?php

namespace Coyote\Repositories\Criteria\Microblog;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Contracts\RepositoryInterface;
use Coyote\Repositories\Criteria\Criteria;

class OnlyMine extends Criteria
{
    /**
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        $query = $model->where('microblogs.user_id', $repository->getUserId());
        return $query;
    }
}
