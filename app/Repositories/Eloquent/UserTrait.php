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
    public function firstVisit($userId, $sessionId)
    {
        static $date;

        if ($date !== null) {
            return $date;
        }

        return $date = $this->app[SessionRepositoryInterface::class]->findFirstVisit($userId, $sessionId);
    }
}
