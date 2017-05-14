<?php

namespace Coyote\Services\Notification\Providers\Post\Comment;

use Coyote\Notification;
use Coyote\Services\Notification\Providers\Post\Base;
use Coyote\Services\Notification\Providers\ProviderInterface;

class Login extends Base implements ProviderInterface
{
    const ID = Notification::POST_COMMENT_LOGIN;
    const EMAIL = 'emails.notifications.post.comment.login';
}
