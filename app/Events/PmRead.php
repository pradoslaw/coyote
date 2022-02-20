<?php

namespace Coyote\Events;

use Illuminate\Broadcasting\Channel;

class PmRead extends PmCreated
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new Channel('user:' . $this->pm->author_id),
            new Channel('user:' . $this->pm->user_id)
        ];
    }

    /**
     * @return array
     */
    public function broadcastWith()
    {
        $this->pm->read_at = now();

        return $this->pm->only(['text_id', 'read_at']);
    }

    /**
     * @return string
     */
    public function broadcastAs()
    {
        return class_basename(self::class);
    }
}
