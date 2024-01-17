<?php
namespace Tests\Unit\Seo\BreadcrumbList;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use Tests\Unit\BaseFixture\Laravel;

trait Models
{
    use Laravel\Application, Laravel\Transactional;

    function newThread(string $topicTitle, string $forumSlug): int
    {
        $forum = $this->newForum('irrelevant', $forumSlug);
        $topic = new Topic;
        $topic->title = $topicTitle;
        $topic->forum_id = $forum->id;
        $topic->save();
        $placeholder = $this->placeholderPost($topic);
        $topic->first_post_id = $placeholder->id;
        $topic->save();
        return $topic->id;
    }

    function newForum(string $name, string $slug): Forum
    {
        $forum = new Forum;
        $forum->name = $name;
        $forum->slug = $slug;
        $forum->description = 'irrelevant';
        $forum->save();
        return $forum;
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
