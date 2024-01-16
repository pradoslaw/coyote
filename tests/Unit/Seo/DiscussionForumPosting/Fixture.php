<?php
namespace Tests\Unit\Seo\DiscussionForumPosting;

use Coyote\Topic;
use Tests\Unit\Seo;

trait Fixture
{
    use Seo\Fixture\Schema, Seo\DiscussionForumPosting\Models;

    function schemaTopicInForum(string $topicTitle, string $forumSlug): array
    {
        $topic = $this->newThread($topicTitle, $forumSlug);
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

    function schemaTopicContent(string $content): array
    {
        return $this->postingSchema($this->newThreadPostContent($content));
    }

    function schemaTopicCreatedAt(string $date, string $timezone): array
    {
        return $this->postingSchema($this->newTopicCreatedAt($date, $timezone));
    }

    function postingSchema(Topic $topic): array
    {
        return $this->schema("/Forum/{$topic->forum->slug}/$topic->id", 'DiscussionForumPosting');
    }
}
