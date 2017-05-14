<?php

namespace Coyote\Services\Notification;

use Coyote\Services\Notification\Providers\ProviderInterface;

class Container
{
    /**
     * @var ProviderInterface[]
     */
    protected $notifications = [];

    /**
     * @param ProviderInterface|null $notification
     */
    public function __construct(ProviderInterface $notification = null)
    {
        if ($notification) {
            $this->notifications[] = $notification;
        }
    }

    /**
     * @param ProviderInterface $notification
     * @return $this
     */
    public function attach(ProviderInterface $notification)
    {
        $this->notifications[] = $notification;
        return $this;
    }

    /**
     * Generuje i wysyla powiadomienia
     */
    public function notify()
    {
        $recipients = [];

        foreach ($this->notifications as $notification) {
            $notification->setUsersId(array_diff($notification->getUsersId(), $recipients));

            $recipients = array_merge($recipients, $notification->notify());
        }
    }
}
