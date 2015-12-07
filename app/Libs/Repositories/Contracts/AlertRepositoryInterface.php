<?php

namespace Coyote\Repositories\Contracts;

/**
 * Interface AlertRepositoryInterface
 * @package Coyote\Repositories\Contracts
 */
interface AlertRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $userId
     * @param int $perPage
     * @return mixed
     */
    public function paginate($userId, $perPage = 20);

    /**
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function takeForUser($userId, $limit = 10, $offset = 0);

    /**
     * Mark notifications as read
     *
     * @param array $id
     */
    public function markAsRead(array $id);

    /**
     * Find notification by url and mark it as read
     *
     * @param int $userId
     * @param string $url
     */
    public function markAsReadByUrl($userId, $url);

    /**
     * Gets notification headline for given type. This template is used for Db_Email() class for emails subject
     *
     * @param $typeId
     * @return mixed
     */
    public function headlinePattern($typeId);

    /**
     * Gets notification settings for given user and notification type
     *
     * @param $typeId
     * @param $usersId
     * @return mixed
     */
    public function userSettings($typeId, $usersId);

    /**
     * Gets first unread notification for given user and notification id (object_id)
     *
     * @param int $userId
     * @param string $objectId
     * @param array $columns
     * @return mixed
     */
    public function findByObjectId($userId, $objectId, $columns = ['*']);

    /**
     * One notification can have multiple senders (users). Few users can post comment to your post.
     * In that case notification can be grouped
     *
     * @param $alertId
     * @param $userId
     * @param $senderName
     */
    public function addSender($alertId, $userId, $senderName);
}
