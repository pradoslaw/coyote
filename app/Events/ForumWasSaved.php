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
     * @var Forum|null
     */
    public $original;

    /**
     * @param Forum $forum
     * @param Forum|null $original
     */
    public function __construct(Forum $forum, ?Forum $original)
    {
        $this->forum = $forum;
        $this->original = $original;
    }
}
