<?php

namespace Coyote\Listeners;

use Coyote\Events\WikiWasDeleted;
use Coyote\Events\WikiWasSaved;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Illuminate\Contracts\Queue\ShouldQueue;

class WikiListener implements ShouldQueue
{
    /**
     * @var WikiRepository
     */
    protected $wiki;

    /**
     * @param WikiRepository $wiki
     */
    public function __construct(WikiRepository $wiki)
    {
        $this->wiki = $wiki;
    }

    /**
     * @param WikiWasSaved $event
     */
    public function onWikiSave(WikiWasSaved $event)
    {
        $event->wiki->putToIndex();
    }

    /**
     * @param WikiWasDeleted $event
     */
    public function onWikiDelete(WikiWasDeleted $event)
    {
        $this->wiki->withTrashed()->find($event->wiki['id'])->deleteFromIndex();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Coyote\Events\WikiWasSaved',
            'Coyote\Listeners\WikiListener@onWikiSave'
        );

        $events->listen(
            'Coyote\Events\WikiWasDeleted',
            'Coyote\Listeners\WikiListener@onWikiDelete'
        );
    }
}
