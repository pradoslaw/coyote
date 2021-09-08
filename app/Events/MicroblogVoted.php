<?php

namespace Coyote\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MicroblogVoted implements ShouldBroadcast
{
    public function __construct(public $payload)
    {
    }

    /**
     * @return Channel|Channel[]
     */
    public function broadcastOn()
    {
        return new Channel('microblog');
    }

    /**
     * @return array
     */
    public function broadcastWith()
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function broadcastAs()
    {
        return class_basename(static::class);
    }
}
