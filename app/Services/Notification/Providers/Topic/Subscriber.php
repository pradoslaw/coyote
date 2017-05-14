<?php

namespace Coyote\Services\Notification\Providers\Topic;

use Coyote\Notification;

class Subscriber extends Base
{
    const ID = Notification::TOPIC_SUBSCRIBER;
    const EMAIL = 'emails.notifications.topic.subscriber';
}
