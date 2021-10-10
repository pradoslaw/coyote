<?php

namespace Coyote\Events;

use Coyote\Topic;
use Illuminate\Queue\SerializesModels;

class TopicMoved
{
    use SerializesModels;

    /**
     * @var Topic
     */
    public $topic;

    /**
     * Create a new event instance.
     *
     * @param Topic $topic
     */
    public function __construct(Topic $topic)
    {
        $this->topic = $topic;
    }
}
