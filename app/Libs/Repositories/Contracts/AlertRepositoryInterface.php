<?php

namespace Coyote\Repositories\Contracts;

/**
 * Interface AlertRepositoryInterface
 * @package Coyote\Repositories\Contracts
 */
interface AlertRepositoryInterface extends RepositoryInterface
{
    public function paginate($userId, $perPage = 20);
    public function takeForUser($userId, $limit = 10);
    public function markAsRead($id);
    public function headlinePattern($typeId);
    public function userSettings($typeId, $usersId);
    public function findByObjectId($userId, $objectId, $columns = ['*']);
    public function addSender($alertId, $userId, $senderName);
}
