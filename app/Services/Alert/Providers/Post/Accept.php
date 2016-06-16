<?php

namespace Coyote\Services\Alert\Providers\Post;

use Coyote\Alert;
use Coyote\Services\Alert\Providers\ProviderInterface;

class Accept extends Base implements ProviderInterface
{
    const ID = Alert::POST_ACCEPT;
    const EMAIL = 'emails.alerts.post.accept';
}
