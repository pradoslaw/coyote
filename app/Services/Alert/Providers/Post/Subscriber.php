<?php

namespace Coyote\Services\Alert\Providers\Post;

use Coyote\Alert;
use Coyote\Services\Alert\Providers\ProviderInterface;

class Subscriber extends Base implements ProviderInterface
{
    const ID = Alert::POST_SUBSCRIBER;
    const EMAIL = null;
}
