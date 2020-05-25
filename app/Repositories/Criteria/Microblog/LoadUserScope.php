<?php

namespace Coyote\Repositories\Criteria\Microblog;

use Coyote\Repositories\Criteria\Criteria;
use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Illuminate\Database\Eloquent\Model;

class LoadUserScope extends Criteria
{
    /**
     * @var int|null
     */
    private $userId;

    /**
     * LoadComments constructor.
     * @param int|null $userId
     */
    public function __construct(int $userId = null)
    {
        $this->userId = $userId;
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
