<?php

namespace Coyote\Services\Notification\Providers\Wiki;

use Coyote\Notification;
use Coyote\Services\Notification\Providers\Provider;

class Subscriber extends Provider
{
    const ID = Notification::WIKI_SUBSCRIBER;
    const EMAIL = 'emails.notifications.wiki.subscriber';
}
