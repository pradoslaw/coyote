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
}
