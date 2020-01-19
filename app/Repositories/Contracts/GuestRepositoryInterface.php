<?php

namespace Coyote\Repositories\Contracts;

use Carbon\Carbon;
use Coyote\Guest;
use Coyote\Session;

interface GuestRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $name
     * @param string $value
     * @param string $guestId
     * @return mixed
     */
    public function setSetting(string $name, string $value, string $guestId);

    /**
     * @param string $guestId
     * @return mixed
     */
    public function getSettings(string $guestId);

    /**
     * @param Session $session
     */
    public function save(Session $session);
}
