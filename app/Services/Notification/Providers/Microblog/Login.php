<?php

namespace Coyote\Services\Notification\Providers\Microblog;

use Coyote\Notification;

class Login extends Base
{
    const ID = Notification::MICROBLOG_LOGIN;
    const EMAIL = 'emails.notifications.microblog.login';
}
