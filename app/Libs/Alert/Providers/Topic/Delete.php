<?php

namespace Coyote\Alert\Providers\Topic;

use Coyote\Alert;
use Coyote\Alert\Providers\Provider;

class Delete extends Provider implements Alert\Providers\ProviderInterface
{
    const ID = Alert::TOPIC_DELETE;
    const EMAIL = null;
}
