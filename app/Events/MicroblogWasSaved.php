<?php

namespace Coyote\Events;

use Coyote\Events\Event;
use Coyote\Microblog;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MicroblogWasSaved extends Event
{
    use SerializesModels;

    /**
     * @var Microblog
     */
    public $microblog;

    /**
     * Create a new event instance.
     *
     * @param Microblog $microblog
     */
    public function __construct(Microblog $microblog)
    {
        $this->microblog = $microblog;
    }
}
