<?php

namespace Coyote\Services\Notification;

use Coyote\Repositories\Contracts\NotificationRepositoryInterface as NotificationRepository;
use Illuminate\Notifications\Notification;

class DatabaseChannel
{
    /**
     * @var NotificationRepository
     */
    private $notification;

    /**
     * @param NotificationRepository $notification
     */
    public function __construct(NotificationRepository $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Send the given notification.
     *
     * @param  \Coyote\User  $user
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($user, Notification $notification)
    {
        $data = $notification->toDatabase($user);
        $notification = $user->getUnreadNotification($notification->objectId());

        if (empty($notification->id)) {
            $notification = $this->notification->create($data);
        }

        $notification->senders()->create($notification->sender());
    }
}
