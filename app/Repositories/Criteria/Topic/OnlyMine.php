<?php

namespace Coyote\Repositories\Criteria\Topic;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

class OnlyMine extends Criteria
{
    /**
     * @var int
     */
    private $userId;

    /**
     * @var bool
     */
    private $includePost;

    /**
     * @param int $userId
     * @param bool $includePost
     */
    public function __construct(int $userId, bool $includePost = false)
    {
        $this->userId = $userId;
        $this->includePost = $includePost;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model
            ->fromSub(function (Builder $builder) {
                return $builder
                    ->select('topics.*')
                    ->from('topic_users')
                    ->join('topics', 'topics.id', '=', 'topic_users.topic_id')
                    ->where('user_id', $this->userId)
                    ->when($this->includePost, function (Builder $builder) {
                        $builder->addSelect(new Expression('topic_users.post_id AS user_post_id'));
                    });
            }, 'topics');
    }
}
