<?php

namespace Coyote\Alert\Providers\Post;

use Coyote\Alert;

/**
 * Class Login
 * @package Coyote\Alert\Providers\Post
 */
class Login extends Base implements Alert\Providers\ProviderInterface
{
    const ID = Alert::POST_LOGIN;
    const EMAIL = 'emails.alerts.post.login';
}
