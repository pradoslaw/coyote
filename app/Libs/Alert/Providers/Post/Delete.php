<?php

namespace Coyote\Alert\Providers\Post;

use Coyote\Alert;
use Coyote\Alert\Providers\Provider;

class Delete extends Provider implements Alert\Providers\ProviderInterface
{
    const ID = Alert::POST_DELETE;
    const EMAIL = null;
}
