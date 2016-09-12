<?php

namespace Coyote\Repositories\Criteria\Topic;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Contracts\RepositoryInterface;

class OnlyThoseWithAccess extends \Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess
{
    /**
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $this->subQuery($model, $repository, 'topics.forum_id');
    }
}
