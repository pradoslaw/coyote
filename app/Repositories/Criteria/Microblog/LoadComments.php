<?php

namespace Coyote\Repositories\Criteria\Microblog;

use Coyote\Repositories\Criteria\Criteria;
use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;

class LoadComments extends Criteria
{
    /**
     * @var int|null
     */
    private $userId;

    /**
     * LoadComments constructor.
     * @param int|null $userId
     */
    public function __construct(int $userId = null)
    {
        $this->userId = $userId;
    }

    /**
     * @param Model $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        $model = $model
            ->select(['microblogs.*', 'users.name', new Expression('users.deleted_at IS NULL AS is_active'), 'users.is_blocked', 'photo'])
            ->join('users', 'users.id', '=', 'user_id')
            ->with(['comments' => function ($builder) {
                $builder->select(['microblogs.*', 'users.name', new Expression('users.deleted_at IS NULL AS is_active'), 'users.is_blocked', 'photo'])
                    ->join('users', 'users.id', '=', 'user_id');

                $this->includeVoters($builder);
                $this->includeSubscribers($builder);
            }]);

        $this->includeVoters($model);
        $this->includeSubscribers($model);

        return $model;
    }

    /**
     * @param Model $model
     * @return mixed|bool
     */
    private function includeVoters($model)
    {
        if (empty($this->userId)) {
            return false;
        }

        return $model->addSelect('mv.id AS thumbs_on')->leftJoin('microblog_votes AS mv', function ($join) {
            $join->on('mv.microblog_id', '=', 'microblogs.id')->where('mv.user_id', '=', $this->userId);
        });
    }

    /**
     * @param Model $model
     * @return mixed
     */
    private function includeSubscribers($model)
    {
        if (empty($this->userId)) {
            return false;
        }

        return $model->addSelect('mw.user_id AS subscribe_on')->leftJoin('microblog_subscribers AS mw', function ($join) {
            $join->on('mw.microblog_id', '=', 'microblogs.id')->where('mw.user_id', '=', $this->userId);
        });
    }
}
