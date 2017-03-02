<?php

namespace Coyote\Events;

use Coyote\Job;
use Illuminate\Queue\SerializesModels;

class PaymentPaid
{
    use SerializesModels;

    /**
     * @var Job
     */
    public $job;

    /**
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }
}
