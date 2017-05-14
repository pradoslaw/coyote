<?php

namespace Coyote\Services\Notification\Providers\Post;

use Coyote\Notification;
use Coyote\Services\Notification\Providers\ProviderInterface;

class Accept extends Base implements ProviderInterface
{
    const ID = Notification::POST_ACCEPT;
    const EMAIL = 'emails.notifications.post.accept';
}
