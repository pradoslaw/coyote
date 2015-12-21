<?php

namespace Coyote\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Coyote\Forum;
use Coyote\User;

class ForumPolicy
{
    use HandlesAuthorization;

    private function check($ability, User $user, Forum $forum)
    {
        return $forum->ability($ability, $user->id) || $user->ability($ability);
    }

    public function sticky(User $user, Forum $forum)
    {
        return $this->check('forum-sticky', $user, $forum);
    }

    public function announcement(User $user, Forum $forum)
    {
        return $this->check('forum-announcement', $user, $forum);
    }

    public function lock(User $user, Forum $forum)
    {
        return $this->check('forum-lock', $user, $forum);
    }

    public function move(User $user, Forum $forum)
    {
        return $this->check('forum-move', $user, $forum);
    }

    public function merge(User $user, Forum $forum)
    {
        return $this->check('forum-merge', $user, $forum);
    }

    public function update(User $user, Forum $forum)
    {
        return $this->check('forum-update', $user, $forum);
    }
}
