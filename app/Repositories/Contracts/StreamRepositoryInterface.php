<?php

namespace Coyote\Repositories\Contracts;

interface StreamRepositoryInterface extends RepositoryInterface
{
    /**
     * Take X last activities
     *
     * @param $limit
     * @param int $offset
     * @param array $objects
     * @param array $verbs
     * @param array $targets
     * @return mixed
     */
    public function take($limit, $offset = 0, $objects = [], $verbs = [], $targets = []);

    /**
     * @param int[] $forumIds
     * @return mixed
     */
    public function forumFeeds(array $forumIds);

    /**
     * @param int $topicId
     * @return mixed
     */
    public function takeForTopic($topicId);
}
