<?php

namespace Coyote\Repositories\Criteria\Topic;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;

class OnlyMine extends Criteria
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
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->fromRaw("(SELECT topics.*, topic_users.post_id AS user_post_id FROM topic_users JOIN topics on topics.id = topic_users.topic_id WHERE user_id = $this->userId) as topics");
    }
}
