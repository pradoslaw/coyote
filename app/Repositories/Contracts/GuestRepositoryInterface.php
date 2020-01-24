<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Session;

interface GuestRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Session $session
     */
    public function save(Session $session);
}
