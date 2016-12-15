<?php

namespace Coyote\Models\Scopes;

use Coyote\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;

trait TrackTopic
{
    /**
     * Scope a query to only given user id.
     *
     * @param Builder $builder
     * @param int $userId
     * @param string $sessionId
     * @return Builder
     */
    public function scopeTrackTopic(Builder $builder, $userId, $sessionId)
    {
        return $builder
            ->addSelect(['topic_track.marked_at AS topic_marked_at'])
            ->leftJoin('topic_track', function (JoinClause $join) use ($userId, $sessionId) {
                $join->on('topic_track.topic_id', '=', 'topics.id');

                if ($userId) {
                    $join->on('topic_track.user_id', '=', new Expression($userId));
                } else {
                    $join->on('topic_track.session_id', '=', new Str($sessionId));
                }
            });
    }
}
