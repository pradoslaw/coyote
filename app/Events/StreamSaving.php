<?php

namespace Coyote\Events;

use Coyote\Services\Stream\Activities\Activity;
use Illuminate\Queue\SerializesModels;

class StreamSaving
{
    use SerializesModels;

    /**
     * @var Activity
     */
    public $activity;

    /**
     * @param Activity $activity
     */
    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }
}
