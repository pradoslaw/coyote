<?php

namespace Coyote\Listeners;

use Coyote\Events\MicroblogWasDeleted;
use Coyote\Events\MicroblogWasSaved;
use Coyote\Events\TopicWasSaved;
use Coyote\Events\TopicWasDeleted;
use Coyote\Microblog;
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
     * @param TopicWasSaved $event
     */
    public function onTopicSave(TopicWasSaved $event)
    {
        $event->topic->page()->updateOrCreate([
            'content_id' => $event->topic->id,
            'content_type' => Topic::class
        ], [
            'title' => $event->topic->subject,
            'path' => route('forum.topic', [$event->topic->forum->path, $event->topic->id, $event->topic->path], false)
        ]);
    }

    /**
     * @param TopicWasDeleted $event
     */
    public function onTopicDelete(TopicWasDeleted $event)
    {
        $this->page->findByContent($event->topic['id'], Topic::class)->delete();
    }

    /**
     * @param MicroblogWasSaved $event
     */
    public function onMicroblogSave(MicroblogWasSaved $event)
    {
        $event->microblog->page()->updateOrCreate([
            'content_id'    => $event->microblog->id,
            'content_type'  => Microblog::class,
        ], [
            'title' => excerpt($event->microblog->text, 28),
            'path' => route('microblog.view', [$event->microblog->id], false)
        ]);
    }

    /**
     * @param MicroblogWasDeleted $event
     */
    public function onMicroblogDelete(MicroblogWasDeleted $event)
    {
        $this->page->findByContent($event->microblog['id'], Microblog::class)->delete();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Coyote\Events\TopicWasSaved',
            'Coyote\Listeners\PageListener@onTopicSave'
        );

        $events->listen(
            'Coyote\Events\TopicWasDeleted',
            'Coyote\Listeners\PageListener@onTopicDelete'
        );

        $events->listen(
            'Coyote\Events\MicroblogWasSaved',
            'Coyote\Listeners\PageListener@onMicroblogSave'
        );

        $events->listen(
            'Coyote\Events\MicroblogWasDeleted',
            'Coyote\Listeners\PageListener@onMicroblogDelete'
        );
    }
}
