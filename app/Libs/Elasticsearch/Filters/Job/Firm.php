<?php

namespace Coyote\Elasticsearch\Filters\Job;

use Coyote\Elasticsearch\DslInterface;
use Coyote\Elasticsearch\Filters\Term;

class Firm extends Term implements DslInterface
{
    /**
     * Firm constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct('firm.name', mb_strtolower($name));
    }
}