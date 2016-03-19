<?php

namespace Coyote\Elasticsearch\Job;

use Coyote\Elasticsearch\DslInterface;
use Coyote\Elasticsearch\Filters\Term;

class Remote extends Term implements DslInterface
{
    /**
     * Remote constructor.
     */
    public function __construct()
    {
        parent::__construct('is_remote', 1);
    }
}