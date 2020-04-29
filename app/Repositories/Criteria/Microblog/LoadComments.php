<?php

namespace Coyote\Repositories\Criteria\Microblog;

use Coyote\Repositories\Criteria\Criteria;
use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
            ->select(['microblogs.*'])
            ->with([
                'user' => function (BelongsTo $builder) {
                    return $builder->select(['id', 'name', 'deleted_at', 'is_blocked', 'photo'])->withTrashed();
                },
                'comments' => function (HasMany $builder) {
                    $builder->select('microblogs.*')->with(['user' => function ($query) {
                        return $query->select(['id', 'name', 'photo', 'deleted_at', 'is_blocked'])->withTrashed();
                    }]);

                    $this->includeVoters($builder);
                    $this->includeSubscribers($builder);
                }
            ]);

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

        return $model
            ->addSelect(new Expression('CASE WHEN mv.id IS NULL THEN false ELSE true END AS is_voted'))
            ->leftJoin('microblog_votes AS mv', function ($join) {
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

        return $model
            ->addSelect(new Expression('CASE WHEN mw.user_id IS NULL THEN false ELSE true END AS is_subscribed'))
            ->leftJoin('microblog_subscribers AS mw', function ($join) {
                $join->on('mw.microblog_id', '=', 'microblogs.id')->where('mw.user_id', '=', $this->userId);
            });
    }
}
