<?php

namespace Coyote\Policies;

use Coyote\Microblog;
use Coyote\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MicroblogPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Microblog $microblog)
    {
        return $user->id === $microblog->user_id || $user->check('microblog-update');
    }

    public function delete(User $user, Microblog $microblog)
    {
        return $user->id === $microblog->user_id || $user->check('microblog-delete');
    }
}
