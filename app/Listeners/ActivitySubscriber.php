<?php

namespace Coyote\Listeners;

use Coyote\Events\CommentDeleted;
use Coyote\Events\CommentSaved;
use Coyote\Events\PostWasDeleted;
use Coyote\Events\PostWasSaved;
use Coyote\Post;
use Coyote\Repositories\Contracts\ActivityRepositoryInterface as ActivityRepository;

class ActivitySubscriber
{
    /**
     * @var ActivityRepository
     */
    private $activity;

    /**
     * ActivitySubscriber constructor.
     * @param ActivityRepository $activity
     */
    public function __construct(ActivityRepository $activity)
    {
        $this->activity = $activity;
    }

    /**
     * @param PostWasSaved $event
     */
    public function onPostSaved(PostWasSaved $event)
    {
        $this->activity->updateOrCreate([
            'content_type'  => Post::class,
            'content_id'    => $event->post->id,
        ], [
            'user_id'       => $event->post->user_id,
            'forum_id'      => $event->post->forum_id,
            'excerpt'       => excerpt($event->post->text)
        ]);
    }

    /**
     * @param PostWasDeleted $event
     */
    public function onPostDeleted(PostWasDeleted $event)
    {
        $this->activity->firstOrNew(['content_id' => $event->post['id'], 'content_type' => Post::class])->delete();
    }

    /**
     * @param CommentSaved $event
     */
    public function onCommentSaved(CommentSaved $event)
    {
        $this->activity->updateOrCreate([
            'content_id'    => $event->comment->id,
            'content_type'  => Post\Comment::class,
        ], [
            'user_id'       => $event->comment->user_id,
            'forum_id'      => $event->comment->post->forum_id,
            'excerpt'       => excerpt($event->comment->post->text)
        ]);
    }

    /**
     * @param CommentDeleted $event
     */
    public function onCommentDeleted(CommentDeleted $event)
    {
        $this->activity->firstOrNew(['content_id' => $event->comment['id'], 'content_type' => Post\Comment::class])->delete();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            PostWasSaved::class,
            'Coyote\Listeners\ActivitySubscriber@onPostSaved'
        );

        $events->listen(
            PostWasDeleted::class,
            'Coyote\Listeners\ActivitySubscriber@onPostDeleted'
        );

        $events->listen(
            CommentSaved::class,
            'Coyote\Listeners\ActivitySubscriber@onCommentSaved'
        );

        $events->listen(
            CommentDeleted::class,
            'Coyote\Listeners\ActivitySubscriber@onCommentDeleted'
        );
    }
}
