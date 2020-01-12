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
     * @param string|null $guestId
     * @return Builder
     */
    public function scopeLoadTopicMarkTime(Builder $builder, ?string $guestId)
    {
        if ($guestId === null) {
            return $builder;
        }

        return $builder
            ->addSelect(['topic_track.marked_at AS read_at'])
            ->leftJoin('topic_track', function (JoinClause $join) use ($guestId) {
                $join
                    ->on('topic_track.topic_id', '=', 'topics.id')
                    ->on('topic_track.guest_id', '=', new Str($guestId));
            });
    }
}
