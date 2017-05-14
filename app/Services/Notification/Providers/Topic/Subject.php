<?php

namespace Coyote\Services\Notification\Providers\Topic;

use Coyote\Notification;

class Subject extends Base
{
    const ID = Notification::TOPIC_SUBJECT;
    const EMAIL = 'emails.notifications.topic.subject';
}
