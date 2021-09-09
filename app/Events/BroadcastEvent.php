<?php

namespace Coyote\Events;

abstract class BroadcastEvent
{
    /**
     * @return string
     */
    public function broadcastAs(): string
    {
        return class_basename(static::class);
    }
}
