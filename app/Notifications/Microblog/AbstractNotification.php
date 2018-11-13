<?php

namespace Coyote\Notifications\Microblog;

use Coyote\Microblog;
use Coyote\Services\Notification\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class AbstractNotification extends Notification implements ShouldQueue, ShouldBroadcast
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

    /**
     * Generowanie unikalnego ciagu znakow dla wpisu na mikro
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(class_basename($this) . $this->microblog->parent_id ?: $this->microblog->id), 16);
    }
}
