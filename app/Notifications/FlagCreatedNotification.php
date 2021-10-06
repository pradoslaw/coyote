<?php

namespace Coyote\Notifications;

use Coyote\Flag;
use Coyote\Services\Notification\DatabaseChannel;
use Coyote\Services\Notification\Notification;
use Coyote\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Channels\BroadcastChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class FlagCreatedNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    const ID = \Coyote\Notification::FLAG;

    /**
     * @var Flag
     */
    private $flag;

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
    public function via(User $user)
    {
        $this->broadcastChannel = 'user:' . $user->id;

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
            'id'            => $this->id
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
     * @return BroadcastMessage
     */
    public function toBroadcast()
    {
        return new BroadcastMessage([
            'headline'  => $this->flag->user->name . ' dodał nowy raport',
            'subject'   => $this->flag->type->name,
            'url'       => $this->redirectionUrl()
        ]);
    }

    public function toWebPush(): WebPushMessage
    {
        return (new WebPushMessage())
            ->title($this->flag->user->name . ' dodał nowy raport')
            ->icon('/img/favicon.png')
            ->body($this->flag->type->name)
            ->tag($this->redirectionUrl())
            ->data(['url' => $this->redirectionUrl()])
            ->options(['TTL' => 1000]);
    }

    protected function channels(User $user): array
    {
        return [DatabaseChannel::class, BroadcastChannel::class, WebPushChannel::class];
    }
}
