<?php

namespace Coyote\Services\Alert\Providers\Topic;

use Coyote\Alert;
use Coyote\Services\Alert\Providers\ProviderInterface;

class Delete extends Base implements ProviderInterface
{
    const ID = Alert::TOPIC_DELETE;
    const EMAIL = null;
}
