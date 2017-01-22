<?php

namespace Coyote\Notifications;

use Coyote\Alert;
use Coyote\Flag;
use Coyote\Services\Alert\DatabaseChannel;
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
        $settings = $user->notificationSetting(static::ID);

        if (!$settings->profile && !$settings->email) {
            return [];
        }

        $this->broadcast[] = 'user:' . $user->id;
        $notification = $user->getUnreadNotification($this->objectId());

        $channels = [DatabaseChannel::class];

        if (empty($notification->id)) {
            $channels[] = 'mail';
        }

        return $channels;
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
                    ->line('Właśnie dodano nowy raport')
                    ->action('Notification Action', 'https://laravel.com');
    }

    /**
     * @param \Coyote\User $user
     * @return array
     */
    public function toDatabase($user)
    {
        return [
            'object_id' => $this->objectId(),
            'user_id' => $user->id,
            'type_id' => static::ID,
            'subject' => $this->flag->type->name,
            'excerpt' => $this->flag->text,
            'url' => $this->flag->url
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
}
