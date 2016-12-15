<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\SessionRepositoryInterface;

trait UserTrait
{
    /**
     * Return date of user's last visit, even if user is guest.
     *
     * @param int $userId
     * @param string $sessionId
     * @return string
     */
    protected function getUserLastVisit($userId, $sessionId)
    {
        static $date;

        if ($date !== null) {
            return $date;
        }

        $date = $this->app[SessionRepositoryInterface::class]->visitedAt($userId, $sessionId);
        return $date;
    }
}
