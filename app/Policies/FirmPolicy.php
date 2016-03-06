<?php

namespace Coyote\Policies;

use Coyote\Firm;
use Coyote\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FirmPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Firm $firm
     * @return bool
     */
    public function update(User $user, Firm $firm)
    {
        return $user->id === $firm->user_id || $user->ability('firm-update');
    }

    /**
     * @param User $user
     * @param Firm $firm
     * @return bool
     */
    public function delete(User $user, Firm $firm)
    {
        return $user->id === $firm->user_id || $user->ability('firm-delete');
    }
}
