<?php

namespace Coyote\Repositories\Criteria\Microblog;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Contracts\RepositoryInterface;
use Coyote\Repositories\Criteria\Criteria;

class OnlyMine extends Criteria
{
    /**
     * @var int
     */
    protected $userId;

    /**
     * OnlyMine constructor.
     * @param int $userId
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        $query = $model->where('microblogs.user_id', $this->userId);
        return $query;
    }
}
