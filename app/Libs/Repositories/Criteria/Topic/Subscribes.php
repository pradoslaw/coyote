<?php

namespace Coyote\Repositories\Criteria\Topic;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;

class Subscribes extends Criteria
{
    /**
     * @var int
     */
    private $userId;

    /**
     * @param $userId
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->subscribes($this->userId);
    }
}
