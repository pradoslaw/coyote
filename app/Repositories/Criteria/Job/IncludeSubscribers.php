<?php

namespace Coyote\Repositories\Criteria\Job;

use Coyote\Job;
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
            return $builder->addSelect(['subscriptions.created_at AS subscribe_on'])
                ->leftJoin('subscriptions', function (JoinClause $join) use ($repository) {
                    $join->on('subscriptions.resource_id', '=', 'jobs.id')->where('subscriptions.resource_type', Job::class)->where('subscriptions.user_id', $this->userId);
                });
        });
    }
}
