<?php

namespace Coyote\Repositories\Criteria\Forum;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Contracts\RepositoryInterface;
use Coyote\Repositories\Criteria\Criteria;

class TreeViewOrder extends Criteria
{
    /**
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model
            ->orderBy($repository->raw('COALESCE(parent_id, id), parent_id IS NOT NULL, "order"'))
            ->groupBy(['parent_id', 'forums.id']);
    }
}
