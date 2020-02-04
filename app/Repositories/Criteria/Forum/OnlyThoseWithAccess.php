<?php

namespace Coyote\Repositories\Criteria\Forum;

use Coyote\Forum;
use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;
use Coyote\User;
use Illuminate\Database\Query\Builder;

class OnlyThoseWithAccess extends Criteria
{
    /**
     * @var array
     */
    private $groupsId = [];

    /**
     * @param \Coyote\User|null $user
     */
    public function __construct($user = null)
    {
        if ($user instanceof User) {
            $this->groupsId = $user->groups()->pluck('id')->toArray();
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        // criteria can be used in multiple models
        $column = $model instanceof Forum ? 'forums.id' : $model->getModel()->getTable() . '.forum_id';

        return $this->applyNested($model, $repository, $column);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @param string $column
     * @return mixed
     */
    protected function applyNested($model, Repository $repository, $column)
    {
        return $model->whereNested(function (Builder $sub) use ($repository, $model, $column) {
            if ($this->hasJoinForumTable($model)) {
                $sub->where('forums.is_prohibited', false);
            } else {
                $sub->whereNotExists(function (Builder $sub) use ($repository, $column) {
                    return $sub->select('forum_id')
                        ->from('forum_access')
                        ->where('forum_access.forum_id', '=', $repository->raw($column));
                });
            }

            if (!empty($this->groupsId)) {
                $sub->orWhereExists(function (Builder $sub) use ($repository, $column) {
                    return $sub->select('forum_id')
                        ->from('forum_access')
                        ->whereIn('group_id', $this->groupsId)
                        ->where('forum_access.forum_id', '=', $repository->raw($column));
                });
            }
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @return bool
     */
    private function hasJoinForumTable($model): bool
    {
        $builder = $model->getQuery();

        /** @var \Illuminate\Database\Query\JoinClause $join */
        foreach ((array) $builder->joins as $join) {
            if ($join->table === 'forums') {
                return true;
            }
        }

        return false;
    }
}
