<?php

namespace Coyote\Services\Notification;

use Coyote\User;

class DatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  NotificationInterface  $notification
     * @return void
     */
    public function send($notifiable, NotificationInterface $notification)
    {
        $data = $notification->toDatabase($notifiable);
        $user = $this->getUser($notifiable);

        // user was removed?
        if ($user === null) {
            return;
        }

        $result = $user->getUnreadNotification($notification->objectId());

        if (empty($result->id)) {
            /** @var \Coyote\Notification $result */
            $result = $user->notifications()->create($data);
        }

        $result->senders()->create($notification->sender());
    }

    /**
     * @param $notifiable
     * @return mixed
     */
    private function getUser($notifiable)
    {
        if ($notifiable instanceof User) {
            return $notifiable;
        }

        if (method_exists($notifiable, 'user')) {
            return $notifiable->user;
        }

        throw new \InvalidArgumentException("Notifiable must have user() method.");
    }
}
