<?php

namespace Coyote\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Coyote\Forum;
use Coyote\User;

class ForumPolicy
{
    use HandlesAuthorization;

    /**
     * @param string $ability
     * @param User $user
     * @param Forum $forum
     * @return bool
     */
    private function check($ability, User $user, Forum $forum)
    {
        return $forum->ability($ability, $user->id) || $user->can($ability);
    }

    /**
     * @param User $user
     * @param Forum $forum
     * @return bool
     */
    public function sticky(User $user, Forum $forum)
    {
        return $this->check('forum-sticky', $user, $forum);
    }

    /**
     * @param User $user
     * @param Forum $forum
     * @return bool
     */
    public function announcement(User $user, Forum $forum)
    {
        return $this->check('forum-announcement', $user, $forum);
    }

    /**
     * @param User $user
     * @param Forum $forum
     * @return bool
     */
    public function lock(User $user, Forum $forum)
    {
        return $this->check('forum-lock', $user, $forum);
    }

    /**
     * @param User $user
     * @param Forum $forum
     * @return bool
     */
    public function move(User $user, Forum $forum)
    {
        return $this->check('forum-move', $user, $forum);
    }

    /**
     * @param User $user
     * @param Forum $forum
     * @return bool
     */
    public function merge(User $user, Forum $forum)
    {
        return $this->check('forum-merge', $user, $forum);
    }

    /**
     * @param User $user
     * @param Forum $forum
     * @return bool
     */
    public function update(User $user, Forum $forum)
    {
        return $this->check('forum-update', $user, $forum);
    }

    /**
     * @param User $user
     * @param Forum $forum
     * @return bool
     */
    public function delete(User $user, Forum $forum)
    {
        return $this->check('forum-delete', $user, $forum);
    }

    /**
     * @param User $user
     * @param Forum $forum
     * @return bool
     */
    public function access(User $user, Forum $forum): bool
    {
        $groups = $forum->groups()->get()->pluck('id')->toArray();

        if (empty($groups)) {
            return true;
        }

        foreach ($user->groups as $group) {
            if (in_array($group->id, $groups)) {
                return true;
            }
        }

        return false;
    }
}
