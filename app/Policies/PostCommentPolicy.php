<?php

namespace Coyote\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Coyote\Forum;
use Coyote\User;
use Coyote\Post\Comment;

class PostCommentPolicy
{
    use HandlesAuthorization;

    /**
     * @param string $ability
     * @param User $user
     * @param Comment $comment
     * @param Forum $forum
     * @return bool
     */
    private function check(string $ability, User $user, Comment $comment, Forum $forum)
    {
        return $user->id === $comment->user_id || $forum->ability($ability, $user->id) || $user->can($ability);
    }

    /**
     * @param User $user
     * @param Comment $comment
     * @param Forum $forum
     * @return bool
     */
    public function update(User $user, Comment $comment, Forum $forum)
    {
        return $this->check('forum-update', $user, $comment, $forum);
    }

    /**
     * @param User $user
     * @param Comment $comment
     * @param Forum $forum
     * @return bool
     */
    public function delete(User $user, Comment $comment, Forum $forum)
    {
        return $this->check('forum-delete', $user, $comment, $forum);
    }
}
