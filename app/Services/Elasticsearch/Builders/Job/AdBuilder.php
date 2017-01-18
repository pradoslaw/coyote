<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Services\Elasticsearch\Functions\Random;
use Coyote\Services\Elasticsearch\QueryBuilder;

class AdBuilder extends SearchBuilder
{
    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @param string $sessionId
     */
    public function setSessionId(string $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return array
     */
    public function build()
    {
        $this->addFilters();
        $this->addFunctionScore();

        $this->scoreFunction(new Random());
        $this->size(0, 4);

        return QueryBuilder::build();
    }

    public function setSort($sort)
    {
        $this->sort($sort);
    }
}
