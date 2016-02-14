<?php

namespace Coyote\Alert\Providers\Topic;

use Coyote\Alert;

class Subject extends Base implements Alert\Providers\ProviderInterface
{
    const ID = Alert::TOPIC_SUBJECT;
    const EMAIL = null;
}
