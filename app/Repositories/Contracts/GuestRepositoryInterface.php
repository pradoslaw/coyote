<?php

namespace Coyote\Repositories\Contracts;

use Carbon\Carbon;
use Coyote\Guest;
use Coyote\Session;

interface GuestRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Session $session
     * @return Guest
     */
    public function store(Session $session): Guest;

    /**
     * @param int|null $userId
     * @param string|null $guestId
     * @return Carbon
     */
    public function createdAt($userId, $guestId = null): Carbon;
}
