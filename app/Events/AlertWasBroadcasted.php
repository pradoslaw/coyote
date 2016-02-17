<?php

namespace Coyote\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Coyote\Alert\Providers\ProviderInterface;

class AlertWasBroadcasted extends Event implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * Alert recipient
     *
     * @var int
     */
    private $userId;

    /**
     * @var ProviderInterface
     */
    public $alert;

    /**
     * Create a new event instance.
     *
     * @param int $userId
     * @param $alert array
     */
    public function __construct($userId, array $alert)
    {
        $this->userId = $userId;
        $this->alert = $alert;
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
        return 'alert';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return $this->alert;
    }
}
