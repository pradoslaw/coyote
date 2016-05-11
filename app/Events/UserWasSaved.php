<?php

namespace Coyote\Events;

use Coyote\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserWasSaved extends Event
{
    use SerializesModels;
    
    protected $userId;

    /**
     * Create a new event instance.
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
