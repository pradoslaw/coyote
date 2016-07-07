<?php

namespace Coyote\Repositories\Criteria;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;

class FlagList extends Criteria
{
    /**
     * @param \Coyote\Flag $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model
            ->withTrashed()
            ->select([
                'flags.*',
                'users.name AS user_name',
                'moderators.name AS moderator_name',
                'flag_types.name AS flag_type'
            ])
            ->leftJoin('flag_types', 'flag_types.id', '=', 'type_id')
            ->leftJoin('users', 'users.id', '=', 'user_id')
            ->leftJoin('users AS moderators', 'moderators.id', '=', 'moderator_id');
    }
}
