<?php

namespace Coyote\Listeners;

use Coyote\Events\TopicWasDeleted;
use Coyote\Events\TopicWasMoved;
use Coyote\Post;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Illuminate\Contracts\Queue\ShouldQueue;

class TopicListener implements ShouldQueue
{
    /**
     * Postpone this job to make sure that record was saved in transaction.
     *
     * @var int
     */
    public $delay = 10;

    /**
     * @var TopicRepository
     */
    protected $topic;

    /**
     * @param TopicRepository $topic
     */
    public function __construct(TopicRepository $topic)
    {
        $this->topic = $topic;
    }

    /**
     * @param TopicWasMoved $event
     */
    public function onTopicMove(TopicWasMoved $event)
    {
        $event->topic->posts()->get()->each(function (Post $post) {
            $post->putToIndex();
        });

        $event->topic->putToIndex();
    }

    /**
     * @param TopicWasDeleted $event
     */
    public function onTopicDelete(TopicWasDeleted $event)
    {
        $topic = $this->topic->withTrashed()->find($event->topic['id']);

        $topic->deleteFromIndex();
        $topic->posts()->withTrashed()->get()->each(function (Post $post) {
            $post->deleteFromIndex();
        });
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Coyote\Events\TopicWasMoved',
            'Coyote\Listeners\TopicListener@onTopicMove'
        );

        $events->listen(
            'Coyote\Events\TopicWasDeleted',
            'Coyote\Listeners\TopicListener@onTopicDelete'
        );
    }
}
