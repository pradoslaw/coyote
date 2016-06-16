<?php

namespace Coyote\Services\Alert\Providers\Post;

use Coyote\Alert;

class Subscriber extends Base
{
    const ID = Alert::POST_SUBSCRIBER;
    const EMAIL = 'emails.alerts.post.subscriber';
}
