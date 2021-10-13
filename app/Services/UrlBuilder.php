<?php

namespace Coyote\Services;

use Coyote\Forum;
use Coyote\Job;
use Coyote\Microblog;
use Coyote\Models\Guide;
use Coyote\Post;
use Coyote\Topic;
use Coyote\Wiki;
use Illuminate\Database\Eloquent\Model;

class UrlBuilder
{
    /**
     * @param Topic $topic
     * @return string
     */
    public static function topic(Topic $topic)
    {
        return route('forum.topic', ['forum' => $topic->forum->slug, 'topic' => $topic->id, 'slug' => $topic->slug], false);
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
     * @param bool $absolute
     * @return string
     */
    public static function post(Post $post, bool $absolute = false)
    {
        return route('forum.topic', ['forum' => $post->forum->slug, 'topic' => $post->topic->id, 'slug' => $post->topic->slug], $absolute) . '?p=' . $post->id . '#id' . $post->id;
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

    /**
     * @param Guide $guide
     * @return string
     */
    public static function guide(Guide $guide): string
    {
        return route('guide.show', [$guide->id, $guide->slug], false);
    }

    public static function url($model): string
    {
        return match ($model::class) {
            Guide::class        => self::guide($model),
            Job::class          => self::job($model),
            Topic::class        => self::topic($model),
            Post::class         => self::post($model),
            Forum::class        => self::forum($model),
            Post\Comment::class => self::postComment($model),
            Wiki::class         => self::wiki($model),
            Microblog::class    => self::microblog($model)
        };
    }
}
