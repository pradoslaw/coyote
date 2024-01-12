<?php
namespace Tests\Unit\Topic\Fixture;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use Tests\Unit\BaseFixture\Laravel;

trait Models
{
    use Laravel\Application, Laravel\Transactional;

    function newTopicTitle(string $title): Topic
    {
        $forum = new Forum;
        $forum->name ??= 'irrelevant';
        $forum->slug ??= 'irrelevant_' . \preg_replace('/\d+/', 'x', uniqid());
        /** we can't set duplicated slug, because RedirectIfMoved
         * doesn't properly handle duplicated slugs, and infinite
         * redirects occur. And of course, {forum} patter in routes,
         * doesn't accept characters.
         * */
        $forum->description = 'irrelevant';
        $forum->save();

        $topic = new Topic;
        $topic->title = $title;
        $topic->forum_id = $forum->id;
        $topic->save();

        $post = new Post;
        $post->text = 'irrelevant';
        $post->ip = 'irrelevant';
        $post->topic_id = $topic->id;
        $post->forum_id = $topic->forum_id;
        $post->save();

        return $topic;
    }
}
