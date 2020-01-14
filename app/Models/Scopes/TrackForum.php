<?php

namespace Coyote\Models\Scopes;

use Coyote\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;

trait TrackForum
{
    /**
     * Scope a query to only given user id.
     *
     * @param Builder $builder
     * @param string|null $guestId
     * @return Builder
     */
    public function scopeWithForumMarkTime(Builder $builder, ?string $guestId)
    {
        if ($guestId === null) {
            return $builder;
        }

        return $builder
            ->addSelect(new Expression('COALESCE(forum_track.marked_at, guests.updated_at) AS read_at'))
            ->leftJoin('forum_track', function (JoinClause $join) use ($guestId) {
                $join
                    ->on('forum_track.forum_id', '=', 'forums.id')
                    ->on('forum_track.guest_id', '=', new Str($guestId));
            })
            ->leftJoin('guests', 'guests.id', '=', new Str($guestId));
    }
}
