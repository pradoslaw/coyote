<?php

namespace Coyote\Services\Notification;

use Coyote\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification as BaseNotification;

abstract class Notification extends BaseNotification implements NotificationInterface
{
    /**
     * @var string|null
     */
    protected $broadcastChannel;

    /**
     * Get the notification's delivery channels.
     *
     * @param  User  $user
     * @return array
     */
    public function via(User $user)
    {
        return $this->getChannels($user);
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [$this->broadcastChannel];
    }

    /**
     * @param User $user
     * @return array
     */
    protected function getChannels(User $user)
    {
        $channels = [];
        $this->broadcastChannel = null;

        $settings = $user->notificationSettings->where('type_id', static::ID)->first();

        if (empty($settings)) {
            return $channels;
        }

        if ($settings->profile) {
            $channels[] = DatabaseChannel::class;
        }

        if (empty($user->getUnreadNotification($this->objectId()))) {
            if ($settings->email && $user->canReceiveEmail()) {
                $channels[] = 'mail';
            }

            if ($settings->profile && $this instanceof ShouldBroadcast) {
                $channels[] = 'broadcast';
                $this->broadcastChannel = $user->receivesBroadcastNotificationsOn();
            }
        }

        return $channels;
    }

    /**
     * @return string
     */
    protected function notificationUrl()
    {
        return route('user.notifications.url', [$this->id]);
    }
}
