<?php

namespace Coyote\Elasticsearch\Job;

use Coyote\Elasticsearch\Dsl;
use Coyote\Elasticsearch\Filters\Term;

class Remote extends Term implements Dsl
{
    /**
     * Remote constructor.
     */
    public function __construct()
    {
        parent::__construct('is_remote', 1);
    }
}