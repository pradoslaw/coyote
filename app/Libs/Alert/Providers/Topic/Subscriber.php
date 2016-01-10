<?php

namespace Coyote\Alert\Providers\Topic;

use Coyote\Alert;

class Subscriber extends Base implements Alert\Providers\ProviderInterface
{
    const ID = Alert::TOPIC_SUBSCRIBER;
    const EMAIL = null;
}
