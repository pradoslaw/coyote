<?php

namespace Coyote\Repositories\Criteria\Microblog;

use Coyote\Repositories\Criteria\Criteria;
use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\User;
use Illuminate\Database\Eloquent\Model;

class LoadUserScope extends Criteria
{
    private int $userId;

    public function __construct(User $user)
    {
        $this->userId = $user->id;
    }

    /**
     * @param Model $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        $model = $model
            ->includeIsVoted($this->userId)
            ->includeIsSubscribed($this->userId);

        return $model;
    }
}
