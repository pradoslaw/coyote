<?php

namespace Coyote\Repositories\Criteria\User;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;

class InSession extends Criteria
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($model, Repository $repository)
    {
        return $model
            ->select(['users.id AS user_id', 'users.name AS name', 'groups.name AS group'])
            ->leftJoin('groups', 'groups.id', '=', $repository->raw('group_id'));
    }
}
