<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Repositories\Contracts\Forum\OrderRepositoryInterface;

/**
 * @property OrderRepositoryInterface $order
 */
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
     * @param int $userId
     * @return mixed
     */
    public function getOrderForUser($userId);

    /**
     * Get restricted access forums.
     *
     * @return int[]
     */
    public function getRestricted();

    /**
     * Builds up a category list that can be shown in a <select>
     *
     * @return array
     */
    public function choices($key = 'slug');

    /**
     * Forum categories as flatten array od models.
     *
     * @return \Coyote\Forum[]
     */
    public function flatten();

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
