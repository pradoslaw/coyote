<?php

namespace Coyote\Services\Alert\Providers\Topic;

use Coyote\Alert;
use Coyote\Services\Alert\Providers\ProviderInterface;

class Subject extends Base implements ProviderInterface
{
    const ID = Alert::TOPIC_SUBJECT;
    const EMAIL = null;
}
