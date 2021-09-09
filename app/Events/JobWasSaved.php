<?php

namespace Coyote\Events;

use Coyote\Job;
use Illuminate\Queue\SerializesModels;

class JobWasSaved
{
    use SerializesModels;

    /**
     * @var Job
     */
    public $job;

    /**
     * Create a new event instance.
     *
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }
}
