<?php

namespace Coyote\Repositories\Criteria\Topic;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;
use Illuminate\Database\Eloquent\Builder;

class BelongsToForum extends Criteria
{
    /**
     * @var int[]
     */
    private $forumsId;

    /**
     * @param int|int[] $forumsId
     */
    public function __construct($forumsId)
    {
        $this->forumsId = (array) $forumsId;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->when(count($this->forumsId) > 0, function (Builder $builder) {
            return $builder->whereIn('topics.forum_id', $this->forumsId);
        });
    }
}
