<?php

namespace Coyote\Repositories\Contracts;

/**
 * @package Coyote\Repositories\Contracts
 */
interface NotificationRepositoryInterface extends RepositoryInterface
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
     * Gets notification settings for given user
     *
     * @param int|int[] $userId
     * @return mixed
     */
    public function getUserSettings($userId);

    /**
     * @param int $userId
     * @param array $data
     */
    public function setUserSettings($userId, array $data);

    /**
     * Gets first unread notification for given user and notification id (object_id)
     *
     * @param int $userId
     * @param string $objectId
     * @param array $columns
     * @return mixed
     */
    public function findByObjectId($userId, $objectId, $columns = ['*']);
}
