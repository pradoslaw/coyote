<?php

namespace Coyote\Events;

use Coyote\Job;
use Illuminate\Queue\SerializesModels;

class JobDeleted
{
    use SerializesModels;

    /**
     * @var array
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
