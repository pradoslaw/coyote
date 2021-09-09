<?php

namespace Coyote\Events;

use Coyote\Microblog;
use Illuminate\Queue\SerializesModels;

class MicroblogDeleted
{
    use SerializesModels;

    /**
     * @var array
     */
    public $microblog;

    /**
     * Create a new event instance.
     *
     * @param Microblog $microblog
     */
    public function __construct(Microblog $microblog)
    {
        $this->microblog = $microblog->only(['id', 'parent_id']);
    }
}
