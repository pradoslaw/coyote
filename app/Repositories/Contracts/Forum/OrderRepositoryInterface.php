<?php

namespace Coyote\Repositories\Contracts\Forum;

use Coyote\Repositories\Contracts\RepositoryInterface;

interface OrderRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $userId
     * @return mixed
     */
    public function takeForUser($userId);
}
