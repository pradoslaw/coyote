<?php

namespace Coyote\Repositories\Contracts\Forum;

use Coyote\Repositories\Contracts\RepositoryInterface;

interface OrderRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $userId
     * @param array $data
     */
    public function saveForUser($userId, array $data);

    /**
     * @param int $userId
     */
    public function deleteForUser($userId);

    /**
     * @param int|null $userId
     * @return array
     */
    public function findHiddenIds($userId);
}
