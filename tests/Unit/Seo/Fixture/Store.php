<?php
namespace Tests\Unit\Seo\Fixture;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use Tests\Unit\BaseFixture\Laravel;

trait Store
{
    use Laravel\Application, Laravel\Transactional;

    function storeThread(Forum $forum, Topic $topic, ?Post $post = null): Topic
    {
        $forum->description = 'irrelevant';
        $forum->slug ??= 'irrelevant_' . \preg_replace('/\d+/', 'x', uniqid());
        /** We can't set duplicated slug, because RedirectIfMoved
         * doesn't properly handle duplicated slugs and infinite
         * redirects occur. Additionally, {forum} pattern in routes
         * doesn't accept digits. */
        $forum->title ??= 'irrelevant';
        $forum->name ??= 'irrelevant';
        $forum->save();

        $topic->forum_id = $forum->id;
        $topic->title ??= 'irrelevant';
        $topic->save();

        if ($post) {
            $post->text ??= 'irrelevant';
            $post->user_name ??= 'irrelevant';
            $post->ip = 'irrelevant';
            $post->forum_id = $forum->id;
            $post->topic_id = $topic->id;
            $post->save();
            $topicPost = $post;
        } else {
            $topicPost = $this->placeholderPost($topic);
        }
        $topic->first_post_id = $topicPost->id;

        $topic->save();
        return $topic;
    }

    function placeholderPost(Topic $topic): Post
    {
        $post = new Post;
        $post->text = 'irrelevant';
        $post->ip = 'irrelevant';
        $post->user_name = 'irrelevant';
        $post->topic_id = $topic->id;
        $post->forum_id = $topic->forum_id;
        $post->save();
        return $post;
    }
}
