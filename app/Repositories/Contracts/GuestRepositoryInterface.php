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
}
