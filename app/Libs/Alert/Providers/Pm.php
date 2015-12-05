<?php

namespace Coyote\Alert\Providers;

use Coyote\Alert;

class Pm extends Provider implements Alert\Providers\ProviderInterface
{
    const ID = Alert::PM;
    const EMAIL = 'emails.alerts.pm';
}
