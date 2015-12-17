<?php

namespace Coyote\Repositories\Contracts;

interface PostRepositoryInterface extends RepositoryInterface
{
    /**
     * Take first post in thread
     *
     * @param int $postId
     * @param int $userId
     * @return mixed
     */
    public function takeFirst($postId, $userId);

    /**
     * Take X posts from topic. IMPORTANT: first post of topic will be always fetched
     *
     * @param int $topicId
     * @param int $postId   First post ID (in thread)
     * @param int $userId
     * @param int $page
     * @param int $perPage
     * @return mixed
     */
    public function takeForTopic($topicId, $postId, $userId, $page = 0, $perPage = 25);
}
