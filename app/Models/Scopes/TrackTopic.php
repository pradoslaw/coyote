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
     * @param string $alias
     * @return Builder
     */
    public function scopeWithTopicMarkTime(Builder $builder, ?string $guestId, string $alias = 'read_at')
    {
        if ($guestId === null) {
            return $builder;
        }

        return $builder
            ->addSelect(["topic_track.marked_at AS $alias"])
            ->leftJoin('topic_track', function (JoinClause $join) use ($guestId) {
                $join
                    ->on('topic_track.topic_id', '=', 'topics.id')
                    ->on('topic_track.guest_id', '=', new Str($guestId));
            });
    }
}
