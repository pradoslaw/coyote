<?php
namespace Coyote\Policies;

use Coyote\Forum;
use Coyote\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForumPolicy
{
    use HandlesAuthorization;

    public function write(?User $user, Forum $forum): bool
    {
        if (!$forum->enable_anonymous && $user === null) {
            return false;
        }

        return !$forum->is_locked ? true : ($user !== null ? $this->update($user, $forum) : false);
    }

    public function update(User $user, Forum $forum): bool
    {
        return $this->check('forum-update', $user, $forum);
    }

    public function sticky(User $user, Forum $forum): bool
    {
        return $this->check('forum-sticky', $user, $forum);
    }

    public function announcement(User $user, Forum $forum): bool
    {
        return $this->check('forum-announcement', $user, $forum);
    }

    public function lock(User $user, Forum $forum): bool
    {
        return $this->check('forum-lock', $user, $forum);
    }

    public function move(User $user, Forum $forum): bool
    {
        return $this->check('forum-move', $user, $forum);
    }

    public function merge(User $user, Forum $forum): bool
    {
        return $this->check('forum-merge', $user, $forum);
    }

    public function delete(User $user, Forum $forum): bool
    {
        return $this->check('forum-delete', $user, $forum);
    }

    public function access(?User $user, Forum $forum): bool
    {
        // field must be present in model
        if ($forum->is_prohibited === false) {
            return true;
        }
        $groups = $forum->groups->pluck('id')->toArray();
        if (empty($groups)) {
            return true;
        }
        // if access to this category is restricted to some groups, it's logical that guest user
        // does not belong to any group.
        if ($user === null) {
            return false;
        }
        foreach ($user->groups as $group) {
            if (in_array($group->id, $groups)) {
                return true;
            }
        }
        return false;
    }

    private function check(string $ability, User $user, Forum $forum): bool
    {
        return $forum->ability($ability, $user->id) || $user->can($ability);
    }
}
