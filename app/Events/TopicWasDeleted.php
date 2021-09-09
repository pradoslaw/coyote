<?php

namespace Coyote\Events;

use Illuminate\Queue\SerializesModels;
use Coyote\Topic;

class TopicWasDeleted
{
    use SerializesModels;

    /**
     * @var array
     */
    public $topic;

    /**
     * Create a new event instance.
     *
     * @param Topic $topic
     */
    public function __construct(Topic $topic)
    {
        $this->topic = $topic->toArray();
    }
}
