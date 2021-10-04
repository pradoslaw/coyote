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

        /** @var array $channels */
        $channels = $user->notificationSettings()->select('channel')->where('type_id', static::ID)->where('is_enabled', true)->pluck('channel')->toArray();

        if (empty($channels)) {
            return [];
        }

        // extra channel: broadcast notification via web socket
        if (in_array(Model::DB, $channels) && $this instanceof ShouldBroadcast) {
            $channels[] = Model::BROADCAST;
        }

        // remove mail channel from the list if user can't get emails (email address is not verified)
        if (in_array(Model::MAIL, $channels) && !$user->canReceiveEmail()) {
            $channels = $this->forget($channels, Model::MAIL);
        }

        // do not send another notification if previous was not yet read
        if (!empty($user->getUnreadNotification($this->objectId()))) {
            $channels = $this->forget($channels, Model::MAIL, Model::PUSH, Model::BROADCAST);
        }

        return $this->resolveChannels($channels);
    }

    /**
     * @param array $channels
     * @return array
     */
    private function resolveChannels(array $channels): array
    {
        $replacement = [Model::DB => DatabaseChannel::class, Model::PUSH => WebPushChannel::class];

        foreach ($replacement as $channel => $class) {
            if (in_array($channel, $channels)) {
                $channels = array_prepend($this->forget($channels, $channel), $class);
            }
        }

        return $channels;
    }

    /**
     * @return string
     */
    protected function notificationUrl(): string
    {
        return route('user.notifications.url', [$this->id]);
    }

    private function forget($channels, ...$values)
    {
        foreach ($values as $value) {
            if (($key = array_search($value, $channels)) !== false) {
                unset($channels[$key]);
            }
        }

        return $channels;
    }
}
