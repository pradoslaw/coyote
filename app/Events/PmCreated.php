<?php

namespace Coyote\Events;

use Coyote\Http\Resources\PmResource;
use Coyote\Pm;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PmCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Pm
     */
    private $pm;

    /**
     * @param Pm $pm
     */
    public function __construct(Pm $pm)
    {
        $this->pm = $pm;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('user:' . $this->pm->author_id);
    }

    /**
     * @return array
     */
    public function broadcastWith()
    {
        return (new PmResource($this->pm))->toArray(request());
    }
}
