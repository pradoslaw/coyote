<?php
namespace Tests\Unit\Canonical\Topic\Fixture;

use Coyote\Forum;
use Coyote\Topic;
use Tests\Unit\BaseFixture;

trait Models
{
    use BaseFixture\Forum\Store;

    function newForumTopic(string $forumSlug, string $topicTitle): int
    {
        $topic = $this->storeThread(
            new Forum(['slug' => $forumSlug]),
            new Topic(['title' => $topicTitle]));
        return $topic->id;
    }

    function newForumSlug(string $slug): void
    {
        $forum = new Forum([
            'slug'        => $slug,
            'name'        => 'irrelevant',
            'description' => 'irrelevant',
        ]);
        $forum->save();
    }
}
