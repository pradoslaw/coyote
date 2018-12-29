<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\StreamRepositoryInterface;
use Coyote\Stream;

class StreamRepository extends Repository implements StreamRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Stream::class;
    }

    /**
     * @param int[] $forumIds
     * @return mixed
     */
    public function forumFeeds(array $forumIds)
    {
        return $this
            ->model
            ->whereIn('object->objectType', ['topic', 'post', 'comment'])
            ->where('verb', 'create')
            ->whereIn('target->objectType', ['forum', 'post', 'topic'])
            ->when($forumIds, function ($query) use ($forumIds) {
                return $query->whereNotIn('object->forum->id', $forumIds)->whereNotIn('target->forum->id', $forumIds);
            })
            ->orderBy('id', 'DESC')
            ->limit(20)
            ->get();
    }

    /**
     * @param int $topicId
     * @return mixed
     */
    public function takeForTopic($topicId)
    {
        return $this
            ->model
            ->whereNested(function ($query) use ($topicId) {
                $query->where('target->objectType', 'topic')
                    ->where('target->id', $topicId);
            })
            ->whereNested(function ($query) use ($topicId) {
                $query->where('object->objectType', 'topic')
                    ->where('object->id', $topicId);
            }, 'or')
            ->orderBy('id', 'DESC')
            ->simplePaginate();
    }

    /**
     * @inheritdoc
     */
    public function hasLoggedBefore($userId, $ip, $browser)
    {
        return $this
            ->model
            ->where('actor->id', $userId)
            ->whereNested(function ($builder) use ($ip, $browser) {
                return $builder->where('ip', $ip)->orWhere('browser', $browser);
            })
            ->exists();
    }
}
