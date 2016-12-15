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
     * @param int $userId
     * @param string $sessionId
     * @return Builder
     */
    public function scopeTrackForum(Builder $builder, $userId, $sessionId)
    {
        return $builder
            ->addSelect(['forum_track.marked_at AS forum_marked_at'])
            ->leftJoin('forum_track', function (JoinClause $join) use ($userId, $sessionId) {
                $join->on('forum_track.forum_id', '=', 'forums.id');

                if ($userId) {
                    $join->on('forum_track.user_id', '=', new Expression($userId));
                } else {
                    $join->on('forum_track.session_id', '=', new Str($sessionId));
                }
            });
    }
}
