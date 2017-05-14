<?php

namespace Coyote\Services\Notification\Providers\Post;

use Coyote\Notification;

class Subscriber extends Base
{
    const ID = Notification::POST_SUBSCRIBER;
    const EMAIL = 'emails.notifications.post.subscriber';
}
