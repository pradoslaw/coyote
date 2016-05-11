<?php

namespace Coyote\Repositories\Contracts;

/**
 * @package Coyote\Repositories\Contracts
 * @method $this withTrashed()
 */
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
     * @param int $userId
     * @param int $limit
     */
    public function interesting($userId, $limit = 7);
}
