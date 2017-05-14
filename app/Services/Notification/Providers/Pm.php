<?php

namespace Coyote\Services\Notification\Providers;

use Coyote\Notification;

class Pm extends Provider implements ProviderInterface
{
    const ID = Notification::PM;
    const EMAIL = 'emails.notifications.pm';
}
