<?php

namespace Coyote\Services\Notification;

interface NotificationInterface
{
    /**
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable);

    /**
     * @return array
     */
    public function sender();

    /**
     * Unique ID for this type of notification. This can be useful for grouping notifications of the same type.
     *
     * @return string
     */
    public function objectId();
}
