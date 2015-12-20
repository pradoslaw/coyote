<?php

namespace Coyote\Repositories\Contracts;

interface ForumRepositoryInterface extends RepositoryInterface
{
    /**
     * Gets categories grouped by sections. You need to pass either $userId or $sessionId (for anonymous users)
     *
     * @param int $userId
     * @param string $sessionId
     * @param null|int $parentId
     * @return mixed
     */
    public function groupBySections($userId, $sessionId, $parentId = null);

    /**
     * Builds up a category list that can be shown in a <select>
     *
     * @return array
     */
    public function forumList();

    /**
     * @param $forumId
     * @param $userId
     * @param $sessionId
     * @return mixed
     */
    public function markTime($forumId, $userId, $sessionId);

    /**
     * @return array
     */
    public function getTagClouds();

    /**
     * Mark forum as read
     *
     * @param $forumId
     * @param $userId
     * @param $sessionId
     */
    public function markAsRead($forumId, $userId, $sessionId);
}
