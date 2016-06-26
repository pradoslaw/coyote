<?php

namespace Coyote\Repositories\Contracts;

interface SubscribableInterface
{
    /**
     * @param int $userId
     * @return mixed
     */
    public function getSubscribed($userId);
}
