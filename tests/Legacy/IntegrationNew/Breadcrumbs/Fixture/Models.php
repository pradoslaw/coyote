<?php
namespace Tests\Legacy\IntegrationNew\Breadcrumbs\Fixture;

use Coyote\Forum;
use Coyote\Topic;
use Tests\Legacy\IntegrationNew\BaseFixture;

trait Models
{
    use BaseFixture\Forum\Store;

    function newTopic(string $forumTitle, string $forumSlug, string $topicTitle): string
    {
        $topic = $this->newThread($topicTitle, $forumTitle, $forumSlug);
        return "/Forum/{$topic->forum->slug}/{$topic->id}-{$topic->slug}";
    }

    function newThread(string $topicTitle, string $forumTitle, string $forumSlug): Topic
    {
        return $this->storeThread(
            new Forum(['name' => $forumTitle, 'slug' => $forumSlug]),
            new Topic(['title' => $topicTitle]));
    }
}
