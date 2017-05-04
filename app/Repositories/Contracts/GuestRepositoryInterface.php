<?php

namespace Coyote\Repositories\Contracts;

use Carbon\Carbon;
use Coyote\Guest;
use Coyote\Session;

interface GuestRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Session $session
     */
    public function save(Session $session);

    /**
     * @param int|null $userId
     * @param string|null $guestId
     * @return Carbon|null
     */
    public function createdAt($userId, $guestId = null);
}
