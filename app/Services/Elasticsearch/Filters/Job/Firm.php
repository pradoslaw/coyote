<?php

namespace Coyote\Services\Elasticsearch\Filters\Job;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filters\Term;

class Firm extends Term implements DslInterface
{
    /**
     * Firm constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct('firm.slug', mb_strtolower($name));
    }
}
