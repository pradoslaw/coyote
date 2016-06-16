<?php

namespace Coyote\Services\Alert\Providers\Wiki;

use Coyote\Alert;
use Coyote\Services\Alert\Providers\Provider;

class Subscriber extends Provider
{
    const ID = Alert::WIKI_SUBSCRIBER;
    const EMAIL = 'emails.alerts.wiki.subscriber';
}
