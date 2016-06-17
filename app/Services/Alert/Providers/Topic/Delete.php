<?php

namespace Coyote\Services\Alert\Providers\Topic;

use Coyote\Alert;

class Delete extends Base
{
    const ID = Alert::TOPIC_DELETE;
    const EMAIL = 'emails.alerts.topic.delete';
}
