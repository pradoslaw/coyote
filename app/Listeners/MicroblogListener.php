<?php

namespace Coyote\Listeners;

use Coyote\Events\MicroblogWasDeleted;
use Coyote\Events\MicroblogSaved;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Services\Elasticsearch\Crawler;
use Illuminate\Contracts\Queue\ShouldQueue;

class MicroblogListener implements ShouldQueue
{
    /**
     * @var MicroblogRepository
     */
    protected $microblog;

    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * @param MicroblogRepository $microblog
     * @param Crawler $crawler
     */
    public function __construct(MicroblogRepository $microblog, Crawler $crawler)
    {
        $this->microblog = $microblog;
        $this->crawler = $crawler;
    }

    /**
     * @param MicroblogSaved $event
     */
    public function onMicroblogSave(MicroblogSaved $event)
    {
        $this->crawler->index($event->microblog);
    }

    /**
     * @param MicroblogWasDeleted $event
     * @throws \Exception
     */
    public function onMicroblogDelete(MicroblogWasDeleted $event)
    {
        $microblog = $this->microblog->withTrashed()->find($event->microblog['id']);

        $this->crawler->delete($microblog);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            MicroblogSaved::class,
            'Coyote\Listeners\MicroblogListener@onMicroblogSave'
        );

        $events->listen(
            MicroblogWasDeleted::class,
            'Coyote\Listeners\MicroblogListener@onMicroblogDelete'
        );
    }
}
