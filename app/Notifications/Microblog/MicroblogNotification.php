<?php

namespace Coyote\Notifications\Microblog;

use Coyote\Microblog;
use Coyote\Services\Notification\Notification;
use Illuminate\Bus\Queueable;

abstract class MicroblogNotification extends Notification
{
    use Queueable;

    /**
     * @var Microblog
     */
    protected $microblog;

    /**
     * @var string
     */
    protected $url;

    /**
     * @param Microblog $microblog
     */
    public function __construct(Microblog $microblog)
    {
        $this->microblog = $microblog;
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'user_id'       => $this->microblog->user_id,
            'name'          => $this->microblog->user->name
        ];
    }
}
