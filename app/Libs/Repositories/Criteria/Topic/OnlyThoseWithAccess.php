<?php

namespace Coyote\Repositories\Criteria\Topic;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Contracts\RepositoryInterface;
use Coyote\Repositories\Criteria\Criteria;

class OnlyThoseWithAccess extends Criteria
{
    /**
     * @var array
     */
    private $groupsId;

    /**
     * @param array $groupsId
     */
    public function __construct(array $groupsId)
    {
        $this->groupsId = $groupsId;
    }

    /**
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        $query = $model->forGroups($this->groupsId);

        return $query;
    }
}
