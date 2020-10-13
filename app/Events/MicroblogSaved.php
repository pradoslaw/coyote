<?php

namespace Coyote\Events;

use Coyote\Http\Resources\Api\MicroblogResource;
use Coyote\Microblog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MicroblogSaved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Microblog
     */
    public $microblog;

    /**
     * Create a new event instance.
     *
     * @param Microblog $microblog
     */
    public function __construct(Microblog $microblog)
    {
        $this->microblog = $microblog;
    }

    public function broadcastOn()
    {
        return new Channel('microblog');
    }

    public function broadcastWith()
    {
        return (new MicroblogResource($this->microblog))->resolve();
    }

    public function broadcastAs()
    {
        return class_basename(self::class);
    }
}
