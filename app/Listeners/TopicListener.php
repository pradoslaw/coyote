<?php

namespace Coyote\Listeners;

use Coyote\Events\TopicWasDeleted;
use Coyote\Events\TopicWasMoved;
use Coyote\Topic;
use Illuminate\Contracts\Queue\ShouldQueue;

class TopicListener implements ShouldQueue
{
    /**
     * @param TopicWasMoved $event
     */
    public function onTopicMove(TopicWasMoved $event)
    {
        $event->topic->posts()->get()->each(function ($post) {
            $post->putToIndex();
        });
    }

    /**
     * @param TopicWasDeleted $event
     */
    public function onTopicDelete(TopicWasDeleted $event)
    {
        Topic::withTrashed()->find($event->topic['id'])->posts()->withTrashed()->get()->each(function ($post) {
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
