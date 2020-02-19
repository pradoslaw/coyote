<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Pm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PmRepositoryInterface extends RepositoryInterface
{
    /**
     * Get last messages
     *
     * @param int $userId
     * @param int $limit
     * @return mixed
     */
    public function groupByAuthor($userId, $limit = 10);

    /**
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function lengthAwarePaginate($userId, $perPage = 10);

    /**
     * Gets conversation
     *
     * @param int $userId
     * @param string $rootId
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function conversation($userId, $rootId, $limit = 10, $offset = 0);

    /**
     * @param int $userId
     * @param int $authorId
     * @return Pm[]
     */
    public function getUnreadIds(int $userId, int $authorId);

    /**
     * Submit a new message
     *
     * @param \Coyote\User $user
     * @param array $payload
     * @throws \Exception
     */
    public function submit(\Coyote\User $user, array $payload);

    /**
     * @param int $userId
     * @param string $rootId
     */
    public function trash($userId, $rootId);

    /**
     * Mark notifications as read
     *
     * @param int $id
     */
    public function markAsRead($id);
}
