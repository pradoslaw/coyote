<?php

namespace Coyote\Services\Elasticsearch\Filters\Job;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filters\Term;

class Remote extends Term implements DslInterface
{
    /**
     * Remote constructor.
     */
    public function __construct()
    {
        parent::__construct('is_remote', true);
    }
}
