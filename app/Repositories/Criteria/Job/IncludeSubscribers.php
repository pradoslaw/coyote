<?php

namespace Coyote\Repositories\Criteria\Job;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

class IncludeSubscribers extends Criteria
{
    /**
     * @var int|null
     */
    private $userId;

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
        return $model->when($this->userId, function (Builder $builder) use ($repository) {
            return $builder->addSelect(['js.created_at AS subscribe_on'])
                ->leftJoin('job_subscribers AS js', function (JoinClause $join) use ($repository) {
                    $join->on('js.job_id', '=', 'jobs.id')->on('js.user_id', '=', $repository->raw($this->userId));
                });
        });
    }
}
