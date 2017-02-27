<?php

namespace Coyote\Notifications;

use Coyote\Alert;
use Coyote\Flag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class FlagCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const ID = Alert::FLAG;

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
        return $this->channels($user);
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
     * @param \Coyote\User $user
     * @return array
     */
    public function toBroadcast($user)
    {
        return [
            'headline'  => $user->name . ' dodał nowy raport',
            'subject'   => $this->flag->type->name,
            'url'       => $this->notificationUrl()
        ];
    }

    /**
     * @param \Coyote\User $user
     * @return mixed
     */
    protected function channels($user)
    {
        $channels = $user->notificationChannels(static::ID);

        $this->broadcast[] = 'user:' . $user->id;
        unset($channels[array_search('email', $channels)]);

        return $channels;
    }

    /**
     * @return string
     */
    protected function notificationUrl()
    {
        return route('user.alerts.url', [$this->id]);
    }
}
