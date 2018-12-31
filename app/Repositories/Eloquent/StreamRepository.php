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
