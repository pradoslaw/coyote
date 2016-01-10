<?php

namespace Coyote\Alert\Providers\Post;

use Coyote\Alert;

class Subscriber extends Base implements Alert\Providers\ProviderInterface
{
    const ID = Alert::POST_SUBSCRIBER;
    const EMAIL = null;
}
