<?php

namespace Coyote\Services\UrlBuilder;

use Coyote\Post;
use Coyote\Topic;

class UrlBuilder
{
    /**
     * @param Topic $topic
     * @return string
     */
    public static function topic(Topic $topic)
    {
        return route('forum.topic', [$topic->forum->slug, $topic->id, $topic->slug], false);
    }

    /**
     * @param Post $post
     * @return string
     */
    public static function post(Post $post)
    {
        return route('forum.topic', [$post->forum->slug, $post->topic->id, $post->topic->slug], false) . '?p=' . $post->id . '#id' . $post->id;
    }
}
