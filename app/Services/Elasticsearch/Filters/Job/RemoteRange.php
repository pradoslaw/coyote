<?php

namespace Coyote\Services\Elasticsearch\Filters\Job;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filters\Term;

class RemoteRange extends Term implements DslInterface
{
    /**
     * @param int $value
     */
    public function __construct(int $value = 100)
    {
        parent::__construct('remote_range', $value);
    }
}
