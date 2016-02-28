<?php

namespace Coyote\Events;

use Illuminate\Queue\SerializesModels;

class MicroblogWasDeleted extends Event
{
    use SerializesModels;

    /**
     * @var array
     */
    public $microblog;

    /**
     * Create a new event instance.
     *
     * @param \Coyote\Microblog $microblog
     */
    public function __construct($microblog)
    {
        $this->microblog = $microblog->toArray();
    }
}
