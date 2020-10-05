<?php

namespace Coyote\Repositories\Criteria\Post;

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
                    $join->on('pv.post_id', '=', 'posts.id')->on('pv.user_id', '=', $repository->raw($this->userId));
                })
                ->leftJoin('post_subscribers AS ps', function ($join) use ($repository) {
                    $join->on('ps.post_id', '=', 'posts.id')->on('ps.user_id', '=', $repository->raw($this->userId));
                });
        }

        return $model;
    }
}
