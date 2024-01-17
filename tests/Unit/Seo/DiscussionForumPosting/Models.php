<?php
namespace Tests\Unit\Seo\DiscussionForumPosting;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use Tests\Unit\BaseFixture\Laravel;

trait Models
{
    use Laravel\Application, Laravel\Transactional;

    function newTopicForumSlug(string $forumSlug): Topic
    {
        return $this->newTopic(fn($t, Forum $forum) => $forum->slug = $forumSlug);
    }

    function newTopicTitle(string $title): Topic
    {
        return $this->newTopic(fn(Topic $topic) => $topic->title = $title);
    }

    function newTopicUsername(string $username): Topic
    {
        return $this->newTopic(fn($t, $f, Post $post) => $post->user_name = $username);
    }

    function newTopicReplies(int $replies): Topic
    {
        return $this->newTopic(fn(Topic $topic) => $topic->replies = $replies);
    }

    function newTopic(callable $assign): Topic
    {
        $topic = new Topic;
        $forum = new Forum;
        $post = new Post;

        $assign($topic, $forum, $post);

        $forum->name ??= 'irrelevant';
        $forum->slug ??= 'irrelevant_' . \preg_replace('/\d+/', 'x', uniqid());
        /** we can't set duplicated slug, because RedirectIfMoved
         * doesn't properly handle duplicated slugs, and infinite
         * redirects occur. And of course, {forum} patter in routes,
         * doesn't accept characters.
         * */
        $forum->description = 'irrelevant';
        $forum->save();

        $topic->title ??= 'irrelevant';
        $topic->forum_id = $forum->id;
        $topic->save();

        $post->text = 'irrelevant';
        $post->ip = 'irrelevant';
        $post->topic_id = $topic->id;
        $post->forum_id = $topic->forum_id;
        $post->save();

        return $topic;
    }
}
