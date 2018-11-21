<?php

namespace Coyote\Services\Notification;

class DatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  \Coyote\User  $user
     * @param  Notification  $notification
     * @return void
     */
    public function send($user, Notification $notification)
    {
        $data = $notification->toDatabase($user);

        $result = $user->getUnreadNotification($notification->objectId());

        if (empty($result->id)) {
            /** @var \Coyote\Notification $result */
            $result = $user->notifications()->create($data);
        }

        $result->senders()->create($notification->sender());
    }
}
