<?php

namespace Coyote\Services\Notification;

interface UserNotificationInterface
{
    /**
     * @param \Coyote\User $user
     * @return array
     */
    public function toDatabase($user);

    /**
     * @return array
     */
    public function sender();

    /**
     * Generowanie unikalnego ciagu znakow dla wpisu na mikro
     *
     * @return string
     */
    public function objectId();
}
