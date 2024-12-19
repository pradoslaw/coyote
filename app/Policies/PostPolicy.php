<?php
namespace Coyote\Policies;

use Coyote\Post;
use Coyote\Reputation;
use Coyote\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

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
        return !$this->isFirstPost($post) && ($user->id === $post->topic->firstPost->user_id || $user->can('update', $post->forum));
    }

    private function check(string $ability, User $user, Post $post): bool
    {
        return $user->can(substr($ability, 6), $post->forum);
    }

    private function isLocked(Post $post): bool
    {
        return $post->topic->is_locked || $post->forum->is_locked;
    }

    private function isAuthor(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    private function isFirstPost(Post $post): bool
    {
        return $post->id === $post->topic->first_post_id;
    }

    private function isLastPost(Post $post): bool
    {
        return $post->id === $post->topic->last_post_id;
    }

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
