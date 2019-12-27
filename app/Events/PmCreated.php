<?php

namespace Coyote\Events;

use Coyote\Http\Resources\PmResource;
use Coyote\Pm;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
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
        return new Channel('user:' . $this->pm->user_id);
    }

    /**
     * @return array
     */
    public function broadcastWith()
    {
        $this->pm->setRelation('user', $this->pm->author);

        return (new PmResource($this->pm))->toArray(request());
    }
}
