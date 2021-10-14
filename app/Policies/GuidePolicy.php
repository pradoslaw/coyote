<?php

namespace Coyote\Policies;

use Coyote\Guide;
use Coyote\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GuidePolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Guide $guide
     * @return bool
     */
    public function update(User $user, Guide $guide): bool
    {
        return $user->id === $guide->user_id || $user->can('guide-update');
    }

    /**
     * @param User $user
     * @param Guide $guide
     * @return bool
     */
    public function delete(User $user, Guide $guide): bool
    {
        return $user->id === $guide->user_id || $user->can('guide-delete');
    }

//    /**
//     * @param User $user
//     * @return bool
//     */
//    public function moderate(User $user): bool
//    {
//        return $user->can('microblog-update');
//    }
}
