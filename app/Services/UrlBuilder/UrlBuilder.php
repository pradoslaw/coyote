<?php

namespace Coyote\Services\UrlBuilder;

use Coyote\Microblog;
use Coyote\Post;
use Coyote\Topic;
use Coyote\Wiki;

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

    /**
     * @param Wiki $wiki
     * @return string
     */
    public static function wiki(Wiki $wiki)
    {
        return route('wiki.show', [$wiki->path], false);
    }

    /**
     * @param Wiki $wiki
     * @param int $commentId
     * @return string
     */
    public static function wikiComment(Wiki $wiki, int $commentId)
    {
        return route('wiki.show', [$wiki->path], false) . '#comment-' . $commentId;
    }

    /**
     * @param Microblog $microblog
     * @return string
     */
    public static function microblog(Microblog $microblog)
    {
        return route('microblog.view', [$microblog->id], false);
    }

    /**
     * @param Microblog $parent
     * @param int $commentId
     * @return string
     */
    public static function microblogComment(Microblog $parent, int $commentId)
    {
        return route('microblog.view', [$parent->id], false) . '#comment-' . $commentId;
    }
}
