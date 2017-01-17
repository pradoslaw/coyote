<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Services\Elasticsearch\Functions\Random;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

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
     * @return QueryBuilderInterface
     */
    public function build() : QueryBuilderInterface
    {
        $this->addFilters();
        $this->addFunctionScore();

        $this->queryBuilder->scoreFunction(new Random());
        $this->queryBuilder->size(0, 4);

        return $this->queryBuilder;
    }

    public function setSort($sort)
    {
        $this->queryBuilder->sort($sort);
    }
}
