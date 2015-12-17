<?php

namespace Coyote\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Coyote\Forum;
use Coyote\User;
use Coyote\Post;

class PostPolicy
{
    use HandlesAuthorization;

    private function check($ability, User $user, Post $post, Forum $forum)
    {
        return $user->id === $post->user_id || $forum->ability($ability, $user->id) || $user->ability($ability);
    }

    public function update(User $user, Post $post, Forum $forum)
    {
        return $this->check('forum-update', $user, $post, $forum);
    }

    public function delete(User $user, Post $post, Forum $forum)
    {
        return $this->check('forum-delete', $user, $post, $forum);
    }
}
