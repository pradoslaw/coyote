<?php

namespace Coyote\Services\Notification\Providers\Wiki;

use Coyote\Notification;
use Coyote\Services\Notification\Providers\Provider;

class Comment extends Provider
{
    const ID = Notification::WIKI_COMMENT;
    const EMAIL = 'emails.notifications.wiki.comment';
}
