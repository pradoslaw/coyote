<?php

namespace Coyote\Listeners;

use Coyote\Events\WikiDeleted;
use Coyote\Events\WikiSaved;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Services\Elasticsearch\Crawler;
use Illuminate\Contracts\Queue\ShouldQueue;

class WikiListener implements ShouldQueue
{
    /**
     * @var WikiRepository
     */
    protected $wiki;

    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * @param WikiRepository $wiki
     * @param Crawler $crawler
     */
    public function __construct(WikiRepository $wiki, Crawler $crawler)
    {
        $this->wiki = $wiki;
        $this->crawler = $crawler;
    }

    /**
     * @param WikiSaved $event
     */
    public function onWikiSave(WikiSaved $event)
    {
        $this->crawler->index($event->wiki);
    }

    /**
     * @param WikiDeleted $event
     * @throws \Exception
     */
    public function onWikiDelete(WikiDeleted $event)
    {
        $wiki = $this->wiki->withTrashed()->find($event->wiki['id']);

        $this->crawler->delete($wiki);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Coyote\Events\WikiSaved',
            'Coyote\Listeners\WikiListener@onWikiSave'
        );

        $events->listen(
            'Coyote\Events\WikiDeleted',
            'Coyote\Listeners\WikiListener@onWikiDelete'
        );
    }
}
