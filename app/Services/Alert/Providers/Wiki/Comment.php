<?php

namespace Coyote\Services\Alert\Providers\Wiki;

use Coyote\Alert;
use Coyote\Services\Alert\Providers\Provider;
use Coyote\Services\Alert\Providers\ProviderInterface;

class Comment extends Provider implements ProviderInterface
{
    const ID = Alert::WIKI_COMMENT;
    const EMAIL = null;
}
