<?php

namespace Coyote\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait ForGuest
{
    /**
     * Scope a query to only given user id.
     *
     * @param Builder $builder
     * @param int $guestId
     * @return Builder
     */
    public function scopeForGuest(Builder $builder, $guestId)
    {
        return $builder->where('guest_id', $guestId);
    }
}
