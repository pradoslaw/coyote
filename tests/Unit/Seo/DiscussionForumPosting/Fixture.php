<?php
namespace Tests\Unit\Seo\DiscussionForumPosting;

use Coyote\Topic;
use Tests\Unit\Seo;

trait Fixture
{
    use Seo\Fixture\Schema, Seo\DiscussionForumPosting\Models;

    function schemaForumSlug(string $forumSlug): array
    {
        $topic = $this->newTopicForumSlug($forumSlug);
        return [$this->postingSchema($topic), $topic->id];
    }

    function schemaTopicTitle(string $title): array
    {
        return $this->postingSchema($this->newTopicTitle($title));
    }

    function schemaForumReplies(int $replies): array
    {
        return $this->postingSchema($this->newTopicReplies($replies));
    }

    function postingSchema(Topic $topic): array
    {
        return $this->schema("/Forum/{$topic->forum->slug}/$topic->id", 'DiscussionForumPosting');
    }
}
