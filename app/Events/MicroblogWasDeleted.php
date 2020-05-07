<?php

namespace Coyote\Events;

use Coyote\Microblog;
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
     * @param Microblog $microblog
     */
    public function __construct(Microblog $microblog)
    {
        $this->microblog = array_except($microblog->toArray(), ['media']); // except media because media can contain AWS S3 Class that cannot be serialized
    }
}
