<?php

namespace Coyote\Listeners;

use Coyote\Events\MicroblogWasDeleted;
use Coyote\Events\MicroblogWasSaved;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Illuminate\Contracts\Queue\ShouldQueue;

class MicroblogListener implements ShouldQueue
{
    /**
     * @var MicroblogRepository
     */
    protected $microblog;

    /**
     * @param MicroblogRepository $microblog
     */
    public function __construct(MicroblogRepository $microblog)
    {
        $this->microblog = $microblog;
    }

    /**
     * @param MicroblogWasSaved $event
     */
    public function onMicroblogSave(MicroblogWasSaved $event)
    {
        $event->microblog->putToIndex();
    }

    /**
     * @param MicroblogWasDeleted $event
     */
    public function onTopicDelete(MicroblogWasDeleted $event)
    {
        $this->microblog->withTrashed()->find($event->microblog['id'])->deleteFromIndex();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            MicroblogWasSaved::class,
            'Coyote\Listeners\MicroblogListener@onMicroblogSave'
        );

        $events->listen(
            MicroblogWasDeleted::class,
            'Coyote\Listeners\MicroblogListener@onTopicDelete'
        );
    }
}
