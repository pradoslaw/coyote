<?php

namespace Coyote\Events;

use Coyote\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Coyote\Topic;

class TopicWasDeleted extends Event
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
