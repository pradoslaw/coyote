<?php

namespace Coyote\Alert\Providers\Post\Comment;

use Coyote\Alert;
use Coyote\Alert\Providers\Post\Base;

class Login extends Base implements Alert\Providers\ProviderInterface
{
    const ID = Alert::POST_COMMENT_LOGIN;
    const EMAIL = 'emails.alerts.post.comment.login';
}
