<?php

namespace Coyote\Policies;

use Coyote\Microblog;
use Coyote\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MicroblogPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Microblog $microblog
     * @return bool
     */
    public function update(User $user, Microblog $microblog)
    {
        return $user->id === $microblog->user_id || $user->ability('microblog-update');
    }

    /**
     * @param User $user
     * @param Microblog $microblog
     * @return bool
     */
    public function delete(User $user, Microblog $microblog)
    {
        return $user->id === $microblog->user_id || $user->ability('microblog-delete');
    }
}
