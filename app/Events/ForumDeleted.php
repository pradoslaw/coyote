<?php

namespace Coyote\Events;

use Illuminate\Queue\SerializesModels;
use Coyote\Forum;

class ForumDeleted
{
    use SerializesModels;

    /**
     * @var array
     */
    public $forum;

    /**
     * Create a new event instance.
     *
     * @param Forum $forum
     */
    public function __construct(Forum $forum)
    {
        $this->forum = $forum->toArray();
    }
}
