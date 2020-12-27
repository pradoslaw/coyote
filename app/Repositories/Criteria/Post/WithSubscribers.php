<?php

namespace Coyote\Repositories\Criteria\Post;

use Coyote\Post;
use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;

class WithSubscribers extends Criteria
{
    /**
     * @var int
     */
    protected $userId;

    /**
     * @param int $userId
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
        if ($this->userId) {
            // pobieramy wartosc "id" a nie "created_at" poniewaz kiedys created_at nie bylo zapisywane
            $model = $model->addSelect(['pv.id AS is_voted', 'ps.id AS is_subscribed'])
                ->leftJoin('post_votes AS pv', function ($join) use ($repository) {
                    $join->on('pv.post_id', '=', 'posts.id')->where('pv.user_id', $this->userId);
                })
                ->leftJoin('subscriptions AS ps', function ($join) use ($repository) {
                    $join->on('ps.resource_id', '=', 'posts.id')->where('ps.resource_type', Post::class)->where('ps.user_id', $this->userId);
                });
        }

        return $model;
    }
}
