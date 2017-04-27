<?php

namespace Coyote\Repositories\Contracts;

/**
 * @property \Coyote\Repositories\Contracts\Forum\OrderRepositoryInterface $order
 */
interface ForumRepositoryInterface extends RepositoryInterface
{
    /**
     * Gets categories grouped by sections. You need to pass either $userId or $sessionId (for anonymous users)
     *
     * @param int $userId
     * @param string $guestId
     * @param null|int $parentId
     * @return mixed
     */
    public function categories($userId, $guestId, $parentId = null);

    /**
     * @param int $userId
     * @return mixed
     */
    public function categoriesOrder($userId);

    /**
     * Get restricted access forums.
     *
     * @return int[]
     */
    public function getRestricted();

    /**
     * Sort a list of categories that can be shown in a <select>
     *
     * @return \Coyote\Forum[]
     */
    public function list();

    /**
     * @return array
     */
    public function getTagsCloud();

    /**
     * @param array $tags
     * @return mixed
     */
    public function getTagsWeight(array $tags);

    /**
     * Mark forum as read
     *
     * @param $forumId
     * @param $userId
     * @param $sessionId
     */
    public function markAsRead($forumId, $userId, $sessionId);
}
