<?php

namespace Coyote\Notifications;

use Coyote\Flag;
use Coyote\Services\Notification\DatabaseChannel;
use Coyote\Services\Notification\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Channels\BroadcastChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;

class FlagCreatedNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    const ID = \Coyote\Notification::FLAG;

    /**
     * @var Flag
     */
    private $flag;

    /**
     * @var array
     */
    private $broadcast = [];

    /**
     * @param Flag $flag
     */
    public function __construct(Flag $flag)
    {
        $this->flag = $flag;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  \Coyote\User  $user
     * @return array
     */
    public function via($user)
    {
        $this->broadcast[] = 'user:' . $user->id;

        return $this->channels();
    }

    /**
     * @param \Coyote\User $user
     * @return array
     */
    public function toDatabase($user)
    {
        return [
            'object_id'     => $this->objectId(),
            'user_id'       => $user->id,
            'type_id'       => static::ID,
            'subject'       => $this->flag->type->name,
            'excerpt'       => str_limit($this->flag->text, 250),
            'url'           => $this->flag->url,
            'guid'          => $this->id
        ];
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'user_id'       => $this->flag->user_id,
            'name'          => $this->flag->user->name
        ];
    }

    /**
     * Generowanie unikalnego ciagu znakow dla wpisu na mikro
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(static::ID . $this->flag->url), 16);
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return $this->broadcast;
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
     * @param \Coyote\User $user
     * @return BroadcastMessage
     */
    public function toBroadcast($user)
    {
        return new BroadcastMessage([
            'headline'  => $user->name . ' dodał nowy raport',
            'subject'   => $this->flag->type->name,
            'url'       => $this->notificationUrl()
        ]);
    }

    /**
     * @return mixed
     */
    protected function channels()
    {
        return [DatabaseChannel::class, BroadcastChannel::class];
    }
}
