<?php

namespace Coyote\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationWasBroadcasted extends Event implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * Alert recipient
     *
     * @var int
     */
    private $userId;

    /**
     * @var array
     */
    public $notification;

    /**
     * Create a new event instance.
     *
     * @param int $userId
     * @param $notification array
     */
    public function __construct($userId, array $notification)
    {
        $this->userId = $userId;
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['user:' . $this->userId];
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'notification';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return $this->notification;
    }
}
