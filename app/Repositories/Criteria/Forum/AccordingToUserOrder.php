<?php

namespace Coyote\Repositories\Criteria\Forum;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;
use Illuminate\Database\Query\JoinClause;

class AccordingToUserOrder extends Criteria
{
    /**
     * @var int|null
     */
    protected $userId;

    /**
     * @param int|null $userId
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
        if ($this->userId !== null) {
            $model
                ->addSelect($repository->raw('(CASE WHEN forum_orders.order IS NOT NULL THEN forum_orders.order ELSE forums.order END) AS custom_order'))
                ->leftJoin('forum_orders', function (JoinClause $join) use ($repository) {
                    $join->on('forum_orders.forum_id', '=', 'forums.id')
                            ->on('forum_orders.user_id', '=', $repository->raw($this->userId));
                })->whereNested(function ($query) {
                    $query->where('is_hidden', 0)->orWhereNull('is_hidden');
                })
                ->orderByRaw('"custom_order"');
        } else {
            $model->orderByRaw('"order"');
        }

        return $model;
    }
}
