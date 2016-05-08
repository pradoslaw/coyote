<?php

namespace Coyote\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Coyote\Forum;
use Coyote\User;
use Coyote\Post;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * @param string $ability
     * @param User $user
     * @param Post $post
     * @param Forum $forum
     * @return bool
     */
    private function check($ability, User $user, Post $post, Forum $forum)
    {
        return $user->id === $post->user_id || $forum->ability($ability, $user->id) || $user->ability($ability);
    }

    /**
     * @param User $user
     * @param Post $post
     * @param Forum $forum
     * @return bool
     */
    public function update(User $user, Post $post, Forum $forum)
    {
        return $this->check('forum-update', $user, $post, $forum);
    }

    /**
     * @param User $user
     * @param Post $post
     * @param Forum $forum
     * @return bool
     */
    public function delete(User $user, Post $post, Forum $forum)
    {
        return $this->check('forum-delete', $user, $post, $forum);
    }
}
