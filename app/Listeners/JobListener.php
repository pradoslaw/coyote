<?php

namespace Coyote\Listeners;

use Coyote\Events\JobWasDeleted;
use Coyote\Events\JobWasSaved;
use Coyote\Job;
use Illuminate\Contracts\Queue\ShouldQueue;

class JobListener implements ShouldQueue
{
    use Elasticsearch;

    /**
     * @param JobWasSaved $event
     */
    public function onJobSave(JobWasSaved $event)
    {
        $this->fireJobs(function () use ($event) {
            $event->job->putToIndex();
        });
    }

    /**
     * @param JobWasDeleted $event
     */
    public function onJobDelete(JobWasDeleted $event)
    {
        $this->fireJobs(function () use ($event) {
            Job::withTrashed()->find($event->job['id'])->deleteFromIndex();
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
            'Coyote\Events\JobWasSaved',
            'Coyote\Listeners\JobListener@onJobSave'
        );

        $events->listen(
            'Coyote\Events\JobWasDeleted',
            'Coyote\Listeners\jobListener@onJobDelete'
        );
    }
}
