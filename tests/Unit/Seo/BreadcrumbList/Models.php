<?php
namespace Tests\Unit\Seo\BreadcrumbList;

use Coyote\Forum;
use Coyote\Topic;
use Tests\Unit\Seo;

trait Models
{
    use Seo\Fixture\Store;

    function newThread(string $topicTitle, string $forumSlug): int
    {
        $topic = $this->storeThread(
            new Forum(['slug' => $forumSlug]),
            new Topic(['title' => $topicTitle]));
        return $topic->id;
    }

    function newForum(string $name, string $slug): void
    {
        $forum = new Forum([
            'name'        => $name,
            'slug'        => $slug,
            'description' => 'irrelevant',
        ]);
        $forum->save();
    }
}
