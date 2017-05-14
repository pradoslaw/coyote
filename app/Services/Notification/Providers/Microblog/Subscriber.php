<?php

namespace Coyote\Services\Notification\Providers\Microblog;

use Coyote\Notification;

class Subscriber extends Base
{
    const ID = Notification::MICROBLOG_SUBSCRIBER;
    const EMAIL = 'emails.notifications.microblog.subscriber';
}
