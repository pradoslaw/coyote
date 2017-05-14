<?php

namespace Coyote\Services\Notification\Providers\Microblog;

use Coyote\Notification;

/**
 * Class Vote
 */
class Vote extends Base
{
    const ID = Notification::MICROBLOG_VOTE;
    const EMAIL = 'emails.notifications.microblog.vote';
}
