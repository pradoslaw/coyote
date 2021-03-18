<?php

namespace Coyote\Listeners;

use Coyote\Events\MicroblogDeleted;
use Coyote\Events\MicroblogSaved;
use Coyote\Microblog;
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
        if ($event->microblog->parent_id) {
            return;
        }

        $this->crawler->index($event->microblog);
    }

    /**
     * @param MicroblogDeleted $event
     * @throws \Exception
     */
    public function onMicroblogDelete(MicroblogDeleted $event)
    {
        if ($event->microblog['parent_id']) {
            return;
        }

        $microblog = (new Microblog())->forceFill($event->microblog);

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
            MicroblogDeleted::class,
            'Coyote\Listeners\MicroblogListener@onMicroblogDelete'
        );
    }
}
