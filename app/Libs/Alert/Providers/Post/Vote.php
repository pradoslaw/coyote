<?php

namespace Coyote\Alert\Providers\Post;

use Coyote\Alert;

class Vote extends Base implements Alert\Providers\ProviderInterface
{
    const ID = Alert::POST_VOTE;
    const EMAIL = null;
}
