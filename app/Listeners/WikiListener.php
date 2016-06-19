<?php

namespace Coyote\Listeners;

use Coyote\Events\WikiWasDeleted;
use Coyote\Events\WikiWasSaved;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Searchable;
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
     * Remove page from elasticsearch.
     *
     * @param WikiWasDeleted $event
     */
    public function onWikiDelete(WikiWasDeleted $event)
    {
        (new class($event->wiki['id']) {
            use Searchable;

            /**
             * @var int
             */
            private $wikiId;

            /**
             * @param int $wikiId
             */
            public function __construct($wikiId)
            {
                $this->wikiId = $wikiId;
            }

            /**
             * @return string
             */
            public function getTable()
            {
                return 'wiki';
            }

            /**
             * @return int
             */
            public function getKey()
            {
                return $this->wikiId;
            }
        }
        )->deleteFromIndex();
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
