<?php

namespace Coyote\Listeners;

use Coyote\Events\TopicWasCreated;
use Coyote\Events\TopicWasDeleted;
use Coyote\Repositories\Contracts\PageRepositoryInterface as Page;
use Illuminate\Contracts\Queue\ShouldQueue;
use Coyote\Topic;

class PageListener implements ShouldQueue
{
    /**
     * @var Page
     */
    protected $page;

    /**
     * PageListener constructor.
     * @param Page $page
     */
    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    /**
     * @param TopicWasCreated $event
     */
    public function onTopicCreated(TopicWasCreated $event)
    {
        $event->topic->page()->create([
            'title' => $event->topic->subject,
            'path' => route('forum.topic', [$event->topic->forum->path, $event->topic->id, $event->topic->path], false)
        ]);
    }

    /**
     * @param TopicWasDeleted $event
     */
    public function onTopicDeleted(TopicWasDeleted $event)
    {
        $this->page->where('content_id', $event->topic['id'])->where('content_type', Topic::class)->delete();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Coyote\Events\TopicWasCreated',
            'Coyote\Listeners\PageListener@onTopicCreated'
        );

        $events->listen(
            'Coyote\Events\TopicWasDeleted',
            'Coyote\Listeners\PageListener@onTopicDeleted'
        );
    }
}
