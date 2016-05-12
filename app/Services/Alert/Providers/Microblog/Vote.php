<?php

namespace Coyote\Services\Alert\Providers\Microblog;

use Coyote\Alert;

/**
 * Class Vote
 */
class Vote extends Base
{
    const ID = Alert::MICROBLOG_VOTE;
    const EMAIL = 'emails.alerts.microblog.vote';
}
