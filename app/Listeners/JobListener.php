<?php

namespace Coyote\Listeners;

use Coyote\Events\JobWasDeleted;
use Coyote\Events\JobWasSaved;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;

// Uwaga! Tutaj specjalnie nie implementujemy interfejsu ShouldQueue poniewaz chcemy zeby usuniecie
// czy dodanie oferty do indeksu nastapilo momentalnie.
class JobListener
{
    /**
     * @var JobRepository
     */
    protected $job;

    /**
     * @param JobRepository $job
     */
    public function __construct(JobRepository $job)
    {
        $this->job = $job;
    }

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
        $this->job->withTrashed()->find($event->job['id'])->deleteFromIndex();
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
