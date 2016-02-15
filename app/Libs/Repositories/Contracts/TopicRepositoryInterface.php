<?php

namespace Coyote\Repositories\Contracts;

interface TopicRepositoryInterface extends RepositoryInterface
{
    /**
     * @param $userId
     * @param $sessionId
     * @param string $order
     * @param string $direction
     * @param int $perPage
     * @return mixed
     */
    public function paginate($userId, $sessionId, $order = 'topics.last_post_id', $direction = 'DESC', $perPage = 20);

    /**
     * @param $topicId
     * @param $userId
     * @param $sessionId
     * @return mixed
     */
    public function markTime($topicId, $userId, $sessionId);

    /**
     * Save topic's tags
     *
     * @param int $topicId
     * @param array $tags
     */
    public function setTags($topicId, array $tags);

    /**
     * Enable/disable subscription for this topic
     *
     * @param int $topicId
     * @param int $userId
     * @param bool $flag
     */
    public function subscribe($topicId, $userId, $flag);

    /**
     * Mark topic as read
     *
     * @param $topicId
     * @param $forumId
     * @param $markTime
     * @param $userId
     * @param $sessionId
     */
    public function markAsRead($topicId, $forumId, $markTime, $userId, $sessionId);

    /**
     * Is there any unread topic in this category?
     *
     * @param $forumId
     * @param $markTime
     * @param $userId
     * @param $sessionId
     * @return mixed
     */
    public function isUnread($forumId, $markTime, $userId, $sessionId);

    /**
     * Lock/unlock topic
     *
     * @param int $topicId
     * @param bool $flag
     */
    public function lock($topicId, $flag);

    /**
     * @param int $topicId
     * @param int $value
     */
    public function addViews($topicId, $value = 1);

    /**
     * @param int $limit
     * @return mixed
     */
    public function newest($limit = 7);

    /**
     * @param int $limit
     * @return mixed
     */
    public function voted($limit = 7);

    /**
     * @param int $limit
     */
    public function interesting($limit = 7);
}
