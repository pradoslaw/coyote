<?php

namespace Coyote\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PmRead extends PmCreated implements ShouldBroadcast
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('user:' . $this->pm->author_id);
    }

    /**
     * @return array
     */
    public function broadcastWith()
    {
        $this->pm->read_at = now();

        return $this->pm->only(['text_id', 'read_at']);
    }
}
