<?php

namespace Coyote\Listeners;

use Coyote\Events\JobWasDeleted;
use Coyote\Events\JobWasSaved;
use Coyote\Job;

class JobListener
{
    /**
     * @param JobWasSaved $event
     */
    public function onJobSave(JobWasSaved $event)
    {
        $event->job->putToIndex();
    }

    /**
     * @param JobWasDeleted $event
     */
    public function onJobDelete(JobWasDeleted $event)
    {
        Job::withTrashed()->find($event->job['id'])->deleteFromIndex();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Coyote\Events\JobWasSaved',
            'Coyote\Listeners\JobListener@onJobSave'
        );

        $events->listen(
            'Coyote\Events\JobWasDeleted',
            'Coyote\Listeners\JobListener@onJobDelete'
        );
    }
}
