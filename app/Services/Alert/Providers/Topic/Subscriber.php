<?php

namespace Coyote\Services\Alert\Providers\Topic;

use Coyote\Alert;
use Coyote\Services\Alert\Providers\ProviderInterface;

class Subscriber extends Base implements ProviderInterface
{
    const ID = Alert::TOPIC_SUBSCRIBER;
    const EMAIL = null;
}
