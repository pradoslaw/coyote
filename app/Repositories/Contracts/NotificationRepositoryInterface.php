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
    public function lengthAwarePaginate($userId, $perPage = 20);

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
    public function markAsReadByModel($userId, $url);

    /**
     * Gets public notification types
     *
     * @return mixed
     */
    public function notificationTypes();

    /**
     * @param int $userId
     * @param array $data
     */
    public function updateSettings($userId, array $data);

    public function purge(): void;
}
