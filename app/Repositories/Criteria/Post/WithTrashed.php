<?php

namespace Coyote\Repositories\Criteria\Post;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;

class WithTrashed extends Criteria
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model
            ->withTrashed()
            ->addSelect('remover.name AS remover_name')
            ->leftJoin('users AS remover', 'remover.id', '=', 'remover_id');
    }
}
