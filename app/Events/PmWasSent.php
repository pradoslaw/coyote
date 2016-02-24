<?php

namespace Coyote\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PmWasSent extends Event implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var int
     */
    public $senderId;

    /**
     * @var string
     */
    public $senderName;

    /**
     * @var string
     */
    public $excerpt;

    /**
     * Create a new event instance.
     *
     * @param int $userId
     * @param int $senderId
     * @param string $senderName
     * @param string $excerpt
     */
    public function __construct($userId, $senderId, $senderName, $excerpt)
    {
        $this->userId = $userId;
        $this->senderId = $senderId;
        $this->senderName = $senderName;
        $this->excerpt = $excerpt;
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
        return 'pm';
    }
}
