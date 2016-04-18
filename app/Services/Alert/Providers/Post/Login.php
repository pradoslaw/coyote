<?php

namespace Coyote\Services\Alert\Providers\Post;

use Coyote\Alert;
use Coyote\Services\Alert\Providers\ProviderInterface;

/**
 * Class Login
 */
class Login extends Base implements ProviderInterface
{
    const ID = Alert::POST_LOGIN;
    const EMAIL = 'emails.alerts.post.login';
}
