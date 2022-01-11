<?php

namespace Coyote\Policies;

use Coyote\Reputation;
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
        if (!$this->isLocked($post)
            && $this->isAuthor($user, $post)
            && ($this->isRecentlyAdded($post) || $this->isLastPost($post) || $this->hasEnoughReputationToEditPost($user))
            && !$this->isArchive($post)) {
            return true;
        }

        return $this->check('forum-update', $user, $post);
    }

    /**
     * @param User $user
     * @param Post $post
     * @return bool
     */
    public function delete(User $user, Post $post): bool
    {
        if (!$this->isLocked($post)
            && $this->isAuthor($user, $post)
            && $this->isLastPost($post)
            && !$this->isArchive($post)) {
            return true;
        }

        return $this->check('forum-delete', $user, $post);
    }

    public function accept(User $user, Post $post): bool
    {
        return $post->id !== $post->topic->first_post_id && ($user->id === $post->topic->firstPost->user_id || $user->can('update', $post->forum));
    }

    /**
     * @param string $ability
     * @param User $user
     * @param Post $post
     * @return bool
     */
    private function check(string $ability, User $user, Post $post): bool
    {
        return $user->can(substr($ability, 6), $post->forum);
    }

    private function isLocked(Post $post): bool
    {
        return $post->forum->is_locked // removing (updating etc) in locked category is forbidden
            || $post->topic->is_locked;
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
    private function isLastPost(Post $post): bool
    {
        return $post->id === $post->topic->last_post_id;
    }

    /**
     * @param Post $post
     * @return bool
     */
    private function isRecentlyAdded(Post $post): bool
    {
        return $post->created_at->diffInHours(now()) <= 24;
    }

    private function isArchive(Post $post): bool
    {
        return $post->created_at->diffInMonths(now()) >= 1;
    }

    private function hasEnoughReputationToEditPost(User $user): bool
    {
        return $user->reputation >= Reputation::EDIT_POST;
    }
}
