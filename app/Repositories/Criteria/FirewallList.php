<?php

namespace Coyote\Repositories\Criteria;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;

class FirewallList extends Criteria
{
    /**
     * @param \Coyote\Wiki $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model
            ->select(['firewall.*', 'users.name AS user_name', 'moderators.name AS moderator_name'])
            ->leftJoin('users', 'users.id', '=', 'user_id')
            ->leftJoin('users AS moderators', 'moderators.id', '=', 'moderator_id');
    }
}
