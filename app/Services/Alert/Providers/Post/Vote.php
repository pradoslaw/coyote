<?php

namespace Coyote\Services\Alert\Providers\Post;

use Coyote\Alert;
use Coyote\Services\Alert\Providers\ProviderInterface;

class Vote extends Base implements ProviderInterface
{
    const ID = Alert::POST_VOTE;
    const EMAIL = null;
}
