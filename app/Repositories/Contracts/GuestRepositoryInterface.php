<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Guest;
use Coyote\Session;

interface GuestRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Session $session
     * @return Guest
     */
    public function store(Session $session): Guest;
}
