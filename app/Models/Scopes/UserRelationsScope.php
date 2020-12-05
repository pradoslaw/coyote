<?php

namespace Coyote\Models\Scopes;

use Coyote\Services\Forum\UserDefined;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserRelationsScope implements Scope
{
    private UserDefined $userDefined;

    public function __construct(UserDefined $userDefined)
    {
        $this->userDefined = $userDefined;
    }

    public function apply(Builder $builder, Model $model): void
    {
        if (empty($this->getExcludedUsers())) {
            return;
        }

        $builder->whereNotIn($model->getTable() . '.user_id', $this->getExcludedUsers());
    }

    protected function getExcludedUsers(): ?array
    {
        static $excluded;

        if (auth()->check() && $excluded === null) {
            $excluded = array_pluck(array_where($this->userDefined->followers(auth()->user()), fn ($item) => $item['is_blocked'] === true), 'user_id');
        }

        return $excluded;
    }
}
