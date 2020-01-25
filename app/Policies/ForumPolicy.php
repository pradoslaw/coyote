<?php

namespace Coyote\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Coyote\Forum;
use Coyote\User;

class ForumPolicy
{
    use HandlesAuthorization;

    /**
     * @param User|null $user
     * @param Forum $forum
     * @return bool
     */
    public function write(?User $user, Forum $forum): bool
    {
        if (!$forum->enable_anonymous && $user === null) {
            return false;
        }

        return !$forum->is_locked ? true : ($user !== null ? $this->update($user, $forum) : false);
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
    public function delete(User $user, Forum $forum)
    {
        return $this->check('forum-delete', $user, $forum);
    }

    /**
     * @param User $user
     * @param Forum $forum
     * @return bool
     */
    public function access(?User $user, Forum $forum): bool
    {
        // field must be present in model
        if ($forum->is_prohibited === false) {
            return true;
        }

        $groups = $forum->groups()->get()->pluck('id')->toArray();

        if (empty($groups)) {
            return true;
        }

        // if access to this category is restricted to some groups, it's logical that guest user
        // does not belong to any group.
        if ($user === null) {
            return false;
        }

        foreach ($user->groups()->get() as $group) {
            if (in_array($group->id, $groups)) {
                return true;
            }
        }

        return false;
    }

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
}
