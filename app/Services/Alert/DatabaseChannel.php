<?php

namespace Coyote\Services\Alert;

use Coyote\Repositories\Contracts\AlertRepositoryInterface as AlertRepository;
use Illuminate\Notifications\Notification;

class DatabaseChannel
{
    /**
     * @var AlertRepository
     */
    private $alert;

    /**
     * @param AlertRepository $alert
     */
    public function __construct(AlertRepository $alert)
    {
        $this->alert = $alert;
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
        $alert = $user->getUnreadNotification($notification->objectId());

        if (empty($alert->id)) {
            $alert = $this->alert->create($data + ['guid' => str_random(25)]);
        }

        $alert->senders()->create(['user_id' => $user->id, 'name' => $user->name]);
    }
}
