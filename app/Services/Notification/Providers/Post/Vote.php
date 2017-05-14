<?php

namespace Coyote\Services\Notification\Providers\Post;

use Coyote\Notification;

class Vote extends Base
{
    const ID = Notification::POST_VOTE;
    const EMAIL = 'emails.notifications.post.vote';
}
