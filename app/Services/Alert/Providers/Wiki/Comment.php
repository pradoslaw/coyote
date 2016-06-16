<?php

namespace Coyote\Services\Alert\Providers\Wiki;

use Coyote\Alert;
use Coyote\Services\Alert\Providers\Provider;

class Comment extends Provider
{
    const ID = Alert::WIKI_COMMENT;
    const EMAIL = 'emails.alerts.wiki.comment';
}
