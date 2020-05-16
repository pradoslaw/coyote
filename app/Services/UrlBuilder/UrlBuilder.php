<?php

namespace Coyote\Services\UrlBuilder;

use Coyote\Forum;
use Coyote\Job;
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
     * @param Forum $forum
     * @return string
     */
    public static function forum(Forum $forum)
    {
        return route('forum.category', [$forum->slug], false);
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
     * @param Post\Comment $comment
     * @return string
     */
    public static function postComment(Post\Comment $comment): string
    {
        return route('forum.topic', [$comment->post->forum->slug, $comment->post->topic->id, $comment->post->topic->slug], false)
            . '?p=' . $comment->post->id . '#comment-' . $comment->id;
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
     * @param Job $job
     * @param bool $absolute
     * @return string
     */
    public static function job(Job $job, $absolute = false)
    {
        return route('job.offer', [$job->id, $job->slug], $absolute);
    }

    /**
     * @param Job $job
     * @param int $commentId
     * @return string
     */
    public static function jobComment(Job $job, int $commentId): string
    {
        return route('job.offer', [$job->id, $job->slug]) . '#comment-' . $commentId;
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
     * @param bool $absolute
     * @return string
     */
    public static function microblog(Microblog $microblog, bool $absolute = false)
    {
        return route('microblog.view', [$microblog->id], $absolute);
    }

    /**
     * @param Microblog $comment
     * @param bool $absolute
     * @return string
     */
    public static function microblogComment(Microblog $comment, bool $absolute = false)
    {
        return route('microblog.view', [$comment->parent_id], $absolute) . '#comment-' . $comment->id;
    }
}
