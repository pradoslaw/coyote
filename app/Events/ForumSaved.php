<?php

namespace Coyote\Events;

use Coyote\Forum;
use Illuminate\Queue\SerializesModels;

class ForumSaved
{
    use SerializesModels;

    /**
     * @var Forum
     */
    public $forum;

    /**
     * @var array
     */
    public $original;

    /**
     * ForumWasSaved constructor.
     * @param Forum $forum
     * @param array $original
     */
    public function __construct(Forum $forum, array $original = [])
    {
        $this->forum = $forum;
        $this->original = $original;
    }
}
