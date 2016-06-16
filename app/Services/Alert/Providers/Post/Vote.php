<?php

namespace Coyote\Services\Alert\Providers\Post;

use Coyote\Alert;

class Vote extends Base
{
    const ID = Alert::POST_VOTE;
    const EMAIL = 'emails.alerts.post.vote';
}
