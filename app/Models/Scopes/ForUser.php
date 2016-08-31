<?php

namespace Coyote\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait ForUser
{
    /**
     * Scope a query to only given user id.
     *
     * @param Builder $builder
     * @param int $userId
     * @return Builder
     */
    public function scopeForUser(Builder $builder, $userId)
    {
        return $builder->where('user_id', $userId);
    }
}
