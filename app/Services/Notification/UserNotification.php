<?php

namespace Coyote\Services\Notification;

use Coyote\User;
use Illuminate\Notifications\Notification;

abstract class UserNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param  User  $user
     * @return array
     */
    public function via($user)
    {
        return $this->getChannels($user);
    }

    /**
     * @param User $user
     * @return array
     */
    protected function getChannels(User $user)
    {
        $channels = [];

        $settings = $user->notificationSettings()->where('type_id', static::ID)->first();

        if ($settings->profile) {
            $channels[] = DatabaseChannel::class;
        }

        if ($user->email && $user->is_active && $user->is_confirm && !$user->is_blocked && $settings->email) {
            $channels[] = 'mail';
        }

        return $channels;
    }
}
