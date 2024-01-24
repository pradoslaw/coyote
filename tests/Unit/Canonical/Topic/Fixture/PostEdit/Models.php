<?php
namespace Tests\Unit\Canonical\Topic\Fixture\PostEdit;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Tests\Unit\BaseFixture;

trait Models
{
    use BaseFixture\Forum\Store;

    function newPostWithAuthor(string $forumSlug): array
    {
        $topic = $this->storeThread(
            new Forum(['slug' => $forumSlug]),
            new Topic,
            new Post(['user_id' => $this->newUser()->id]));
        return [$topic->id, $topic->firstPost->id, $topic->firstPost->user];
    }

    function newUser(): User
    {
        $user = new User;
        $user->name = 'irrelevant';
        $user->email = 'irrelevant';
        $user->save();
        return $user;
    }
}
