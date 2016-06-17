<?php

namespace Coyote\Services\Alert\Providers\Topic;

use Coyote\Alert;

class Subscriber extends Base
{
    const ID = Alert::TOPIC_SUBSCRIBER;
    const EMAIL = 'emails.alerts.topic.subscriber';
}
