<?php

namespace Coyote\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Coyote\Forum;
use Coyote\User;
use Coyote\Post\Comment;

class PostCommentPolicy
{
    use HandlesAuthorization;

    private function check($ability, User $user, Comment $comment, Forum $forum)
    {
        return $user->id === $comment->user_id || $forum->ability($ability, $user->id) || $user->ability($ability);
    }

    public function update(User $user, Comment $comment, Forum $forum)
    {
        return $this->check('forum-update', $user, $comment, $forum);
    }

    public function delete(User $user, Comment $comment, Forum $forum)
    {
        return $this->check('forum-delete', $user, $comment, $forum);
    }
}
