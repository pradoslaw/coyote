<?php

namespace Coyote\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserRelationsScope implements Scope
{
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
            $excluded = auth()->user()->relations()->blocked()->pluck('related_user_id')->toArray();
        }

        return $excluded;
    }
}
