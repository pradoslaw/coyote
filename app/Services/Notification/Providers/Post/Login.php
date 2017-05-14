<?php

namespace Coyote\Services\Notification\Providers\Post;

use Coyote\Notification;

/**
 * Class Login
 */
class Login extends Base
{
    const ID = Notification::POST_LOGIN;
    const EMAIL = 'emails.notifications.post.login';
}
