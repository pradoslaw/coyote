<?php

namespace Coyote\Elasticsearch\Filters\Job;

use Coyote\Elasticsearch\DslInterface;
use Coyote\Elasticsearch\Filters\Term;

class Currency extends Term implements DslInterface
{
    /**
     * Firm constructor.
     * @param int $currencyId
     */
    public function __construct($currencyId)
    {
        parent::__construct('currency_id', (int) $currencyId);
    }
}