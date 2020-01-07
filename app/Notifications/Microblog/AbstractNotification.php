<?php

namespace Coyote\Notifications\Microblog;

use Coyote\Microblog;
use Coyote\Services\Notification\Notification;
use Coyote\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

abstract class AbstractNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    /**
     * @var Microblog
     */
    protected $microblog;

    /**
     * @var User
     */
    protected $notifier;

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
        $this->notifier = $this->microblog->user;
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'user_id'       => $this->notifier->id,
            'name'          => $this->notifier->name
        ];
    }

    /**
     * Generowanie unikalnego ciagu znakow dla wpisu na mikro
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(class_basename($this) . ($this->microblog->parent_id ?: $this->microblog->id)), 16);
    }

    /**
     * @return BroadcastMessage
     */
    public function toBroadcast()
    {
        return new BroadcastMessage([
            'headline'  => $this->getMailSubject(),
            'subject'   => excerpt($this->microblog->html),
            'url'       => $this->notificationUrl(),
        ]);
    }

    /**
     * @return string
     */
    abstract protected function getMailSubject(): string;
}
