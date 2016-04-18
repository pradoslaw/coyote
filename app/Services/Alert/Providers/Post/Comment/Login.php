<?php

namespace Coyote\Services\Alert\Providers\Post\Comment;

use Coyote\Alert;
use Coyote\Services\Alert\Providers\Post\Base;
use Coyote\Services\Alert\Providers\ProviderInterface;

class Login extends Base implements ProviderInterface
{
    const ID = Alert::POST_COMMENT_LOGIN;
    const EMAIL = 'emails.alerts.post.comment.login';
}
