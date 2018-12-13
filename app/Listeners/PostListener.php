<?php

namespace Coyote\Listeners;

use Coyote\Events\PostWasDeleted;
use Coyote\Events\PostWasSaved;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostListener implements ShouldQueue
{
    /**
     * @var PostRepository
     */
    protected $post;

    /**
     * @param PostRepository $post
     */
    public function __construct(PostRepository $post)
    {
        $this->post = $post;
    }

    /**
     * @param PostWasSaved $event
     */
    public function onPostSave(PostWasSaved $event)
    {
        $event->post->putToIndex();
        $event->post->topic->putToIndex();
    }

    /**
     * @param PostWasDeleted $event
     */
    public function onPostDelete(PostWasDeleted $event)
    {
        /** @var \Coyote\Post $post */
        $post = $this->post->withTrashed()->find($event->post['id']);

        $post->deleteFromIndex();
        // reindex whole topic
        $post->topic->putToIndex();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Coyote\Events\PostWasSaved',
            'Coyote\Listeners\PostListener@onPostSave'
        );

        $events->listen(
            'Coyote\Events\PostWasDeleted',
            'Coyote\Listeners\PostListener@onPostDelete'
        );
    }
}
