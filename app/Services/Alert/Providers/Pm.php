<?php

namespace Coyote\Services\Alert\Providers;

use Coyote\Alert;

class Pm extends Provider implements ProviderInterface
{
    const ID = Alert::PM;
    const EMAIL = 'emails.alerts.pm';
}
