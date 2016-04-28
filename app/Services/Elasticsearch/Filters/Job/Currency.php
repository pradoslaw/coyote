<?php

namespace Coyote\Services\Elasticsearch\Filters\Job;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filters\Term;

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
