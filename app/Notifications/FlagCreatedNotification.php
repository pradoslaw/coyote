<?php

namespace Coyote\Notifications;

use Coyote\Alert;
use Coyote\Flag;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

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
     * Get the mail representation of the notification.
     *
     * @param  \Coyote\User  $user
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($user)
    {
        return (new MailMessage)
            ->greeting($user->name)
            ->line(sprintf('%s zgłosił naruszenie z powodu %s.', $this->flag->user->name, $this->flag->type->name))
            ->line('Kliknij na poniższy przycisk jeżeli chcesz podjąć w związku z tym jakieś działania.')
            ->action('Zobacz raport', $this->notificationUrl());
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
            'excerpt'       => $this->flag->text,
            'url'           => $this->flag->url,
            'guid'          => $this->id
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
     * @return array
     */
    public function toBroadcast()
    {
        return [];
    }

    /**
     * @param \Coyote\User $user
     * @return mixed
     */
    protected function channels($user)
    {
        $channels = $user->notificationChannels(static::ID);

        $this->broadcast[] = 'user:' . $user->id;
        $notification = $user->getUnreadNotification($this->objectId());

        if (!empty($notification->id)) {
            unset($channels[array_search('email', $channels)]);
        }

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
