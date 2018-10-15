<?php

namespace Coyote\Policies;

use Carbon\Carbon;
use Illuminate\Auth\Access\HandlesAuthorization;
use Coyote\User;
use Coyote\Post;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Post $post
     * @return bool
     */
    public function update(User $user, Post $post): bool
    {
        return $this->check('forum-update', $user, $post);
    }

    /**
     * @param User $user
     * @param Post $post
     * @return bool
     */
    public function delete(User $user, Post $post): bool
    {
        return $this->check('forum-delete', $user, $post);
    }

    /**
     * @param string $ability
     * @param User $user
     * @param Post $post
     * @return bool
     */
    private function check($ability, User $user, Post $post): bool
    {
        return ($this->isAuthor($user, $post) && ($this->isNotOld($post) || $this->hasEnoughReputation($user, $post)))
            || $post->forum->ability($ability, $user->id)
                || $user->can($ability);
    }

    /**
     * @param User $user
     * @param Post $post
     * @return bool
     */
    private function isAuthor(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * @param User $user
     * @param Post $post
     * @return bool
     */
    private function hasEnoughReputation(User $user, Post $post): bool
    {
        return $post->id == $post->topic->last_post_id ? true : ($user->reputation >= 100);
    }

    /**
     * @param Post $post
     * @return bool
     */
    private function isNotOld(Post $post): bool
    {
        return $post->created_at->diffInMinutes(Carbon::now()) < 30;
    }
}
