<?php

namespace Coyote\Alert\Providers\Topic;

use Coyote\Alert;

class Delete extends Base implements Alert\Providers\ProviderInterface
{
    const ID = Alert::TOPIC_DELETE;
    const EMAIL = null;
}
