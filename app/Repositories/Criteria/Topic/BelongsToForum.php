<?php

namespace Coyote\Repositories\Criteria\Topic;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;

class BelongsToForum extends Criteria
{
    /**
     * @var int
     */
    private $forumId;

    /**
     * @param int $forumId
     */
    public function __construct($forumId)
    {
        $this->forumId = $forumId;
    }

    /**
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->where('topics.forum_id', $this->forumId);
    }
}
