<?php

namespace Coyote\Events;

use Illuminate\Queue\SerializesModels;

class UserWasSaved extends Event
{
    use SerializesModels;

    public $userId;

    /**
     * Create a new event instance.
     *
     * @param int $userId
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }
}
