<?php
namespace Tests\Unit\Seo\DiscussionForumPosting;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Tests\Unit\Seo;

trait Models
{
    use Seo\Fixture\Store;

    function newTopicForumSlug(string $forumSlug): Topic
    {
        return $this->storeThread(new Forum(['slug' => $forumSlug]), new Topic);
    }

    function newTopicTitle(string $title): Topic
    {
        return $this->storeThread(new Forum, new Topic(['title' => $title]));
    }

    function newTopicAuthorLegacyGuest(string $username): Topic
    {
        return $this->storeThread(new Forum, new Topic, new Post(['user_name' => $username]));
    }

    function newTopicAuthorUsername(string $username): Topic
    {
        $user = new User;
        $user->name = $username;
        $user->email = 'irrelevant';
        $user->save();
        return $this->storeThread(new Forum, new Topic, new Post(['user_id' => $user->id]));
    }

    function newTopicReplies(int $replies): Topic
    {
        $topic = new Topic;
        $topic->replies = $replies;
        return $this->storeThread(new Forum, $topic);
    }
}
