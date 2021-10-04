<?php

namespace Coyote\Services\Notification;

use Coyote\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification as BaseNotification;
use Coyote\Notification as Model;
use NotificationChannels\WebPush\WebPushChannel;

abstract class Notification extends BaseNotification implements NotificationInterface
{
    /**
     * @var string|null
     */
    public $broadcastChannel;

    /**
     * Get the notification's delivery channels.
     *
     * @param  User  $user
     * @return array
     */
    public function via(User $user)
    {
        return $this->channels($user);
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
    protected function channels(User $user): array
    {
        $this->broadcastChannel = $user->receivesBroadcastNotificationsOn();

        /** @var \Illuminate\Support\Collection $channels */
        $channels = $user->notificationSettings()->select('channel')->where('type_id', static::ID)->where('is_enabled', true)->pluck('channel');

        if (empty($channels)) {
            return [];
        }

        if ($channels->contains(Model::DB) && $this instanceof ShouldBroadcast) {
            $channels->push('broadcast');
        }

        if ($channels->contains(Model::MAIL) && !$user->canReceiveEmail()) {
            $channels->forget(Model::MAIL);
        }

        // do not send another notification if previous was not yet read
        if (!empty($user->getUnreadNotification($this->objectId()))) {
            $channels->forget([Model::MAIL, Model::PUSH]);
        }

        return $this->resolveChannels($channels);
    }

    /**
     * @param \Illuminate\Support\Collection $channels
     * @return array
     */
    private function resolveChannels($channels): array
    {
        $replacement = [Model::DB => DatabaseChannel::class, Model::PUSH => WebPushChannel::class];

        foreach ($replacement as $channel => $class) {
            if ($channels->contains($channel)) {
                $channels = $channels->reject(fn ($value) => $value === $channel)->push($class);
            }
        }

        return $channels->toArray();
    }

    /**
     * @return string
     */
    protected function notificationUrl()
    {
        return route('user.notifications.url', [$this->id]);
    }
}
