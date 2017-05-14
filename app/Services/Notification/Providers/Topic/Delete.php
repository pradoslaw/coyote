<?php

namespace Coyote\Services\Notification\Providers\Topic;

use Coyote\Notification;

class Delete extends Base
{
    const ID = Notification::TOPIC_DELETE;
    const EMAIL = 'emails.notifications.topic.delete';
}
