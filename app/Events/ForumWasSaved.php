<?php

namespace Coyote\Events;

use Coyote\Forum;
use Illuminate\Queue\SerializesModels;

class ForumWasSaved extends Event
{
    use SerializesModels;

    /**
     * @var Forum
     */
    public $forum;

    /**
     * Create a new event instance.
     *
     * @param Forum $forum
     */
    public function __construct(Forum $forum)
    {
        $this->forum = $forum;
    }
}
