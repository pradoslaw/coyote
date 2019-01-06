<?php

namespace Coyote\Repositories\Criteria\Forum;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;
use Illuminate\Database\Query\Builder;

class SkipHiddenCategories extends Criteria
{
    /**
     * @var int|null
     */
    protected $userId;

    /**
     * @param int|null $userId
     */
    public function __construct(?int $userId)
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
        if ($this->userId === null) {
            return $model;
        }

        return $model->whereNotIn('forum_id', function (Builder $sub) use ($repository) {
            return $sub->select('forum_id')
                ->from('forum_orders')
                ->where('user_id', '=', $repository->raw($this->userId))
                ->where('is_hidden', 1);
        });
    }
}
