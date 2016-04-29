<?php

namespace Coyote\Policies;

use Coyote\Pm;
use Coyote\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PmPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Pm $pm
     * @return bool
     */
    public function show(User $user, Pm $pm)
    {
        return $user->id === $pm->user_id;
    }
}
