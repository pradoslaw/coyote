<?php

namespace Coyote\Models\Scopes;

use Coyote\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

trait TrackTopic
{
    /**
     * Scope a query to only given user id.
     *
     * @param Builder $builder
     * @param string $guestId
     * @return Builder
     */
    public function scopeTrackTopic(Builder $builder, string $guestId)
    {
        return $builder
            ->addSelect(['topic_track.marked_at AS topic_marked_at'])
            ->leftJoin('topic_track', function (JoinClause $join) use ($guestId) {
                $join
                    ->on('topic_track.topic_id', '=', 'topics.id')
                    ->on('topic_track.guest_id', '=', new Str($guestId));
            });
    }
}
