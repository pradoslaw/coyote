<?php

namespace Coyote\Services\Notification;

use Coyote\Repositories\Contracts\NotificationRepositoryInterface as NotificationRepository;

class DatabaseChannel
{
    /**
     * @var NotificationRepository
     */
    private $repository;

    /**
     * @param NotificationRepository $repository
     */
    public function __construct(NotificationRepository $repository)
    {
        $this->repository = $repository;
    }

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

        $alert = $user->getUnreadNotification($notification->objectId());

        if (empty($alert->id)) {
            $alert = $this->repository->create($data);
        }

        $alert->senders()->create($notification->sender());
    }
}
