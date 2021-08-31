<?php

namespace Coyote\Listeners;

use Coyote\Events\CommentDeleted;
use Coyote\Events\CommentSaved;
use Coyote\Events\PostWasDeleted;
use Coyote\Events\PostSaved;
use Coyote\Events\TopicWasDeleted;
use Coyote\Events\TopicWasMoved;
use Coyote\Post;
use Coyote\Repositories\Contracts\ActivityRepositoryInterface as ActivityRepository;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActivitySubscriber implements ShouldQueue
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
     * @param PostSaved $event
     */
    public function onPostSaved(PostSaved $event)
    {
        $this->activity->updateOrCreate([
            'content_type'  => Post::class,
            'content_id'    => $event->post->id,
        ], [
            'created_at'    => $event->post->created_at,
            'user_id'       => $event->post->user_id,
            'forum_id'      => $event->post->forum_id,
            'topic_id'      => $event->post->topic_id,
            'excerpt'       => excerpt($event->post->html),
            'user_name'     => $event->post->user_name
        ]);
    }

    /**
     * @param PostWasDeleted $event
     */
    public function onPostDeleted(PostWasDeleted $event)
    {
        $this->activity->where('content_id', $event->post['id'])->where('content_type', Post::class)->delete();
    }

    public function onTopicDeleted(TopicWasDeleted $event)
    {
        $this->activity->where('topic_id', $event->topic['id'])->delete();
    }

    public function onTopicMoved(TopicWasMoved $event)
    {
        $this->activity->where('topic_id', $event->topic['id'])->update(['forum_id' => $event->topic['forum_id']]);
    }

    /**
     * @param CommentSaved $event
     */
    public function onCommentSaved(CommentSaved $event)
    {
        // post was removed?
        if (!$event->comment->post) {
            return;
        }

        $this->activity->updateOrCreate([
            'content_id'    => $event->comment->id,
            'content_type'  => Post\Comment::class,
        ], [
            'created_at'    => $event->comment->created_at,
            'user_id'       => $event->comment->user_id,
            'forum_id'      => $event->comment->post->forum_id,
            'topic_id'      => $event->comment->post->topic_id,
            'excerpt'       => excerpt($event->comment->html)
        ]);
    }

    /**
     * @param CommentDeleted $event
     */
    public function onCommentDeleted(CommentDeleted $event)
    {
        $this->activity->where('content_id', $event->comment['id'])->where('content_type', Post\Comment::class)->delete();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            PostSaved::class,
            'Coyote\Listeners\ActivitySubscriber@onPostSaved'
        );

        $events->listen(
            PostWasDeleted::class,
            'Coyote\Listeners\ActivitySubscriber@onPostDeleted'
        );

        $events->listen(
            TopicWasDeleted::class,
            'Coyote\Listeners\ActivitySubscriber@onTopicDeleted'
        );

        $events->listen(
            TopicWasMoved::class,
            'Coyote\Listeners\ActivitySubscriber@onTopicMoved'
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
