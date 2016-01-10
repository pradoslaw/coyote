<?php

namespace Coyote\Alert\Providers\Post;

use Coyote\Alert;

class Accept extends Base implements Alert\Providers\ProviderInterface
{
    const ID = Alert::POST_ACCEPT;
    const EMAIL = null;
}
