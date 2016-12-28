<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class AdBuilder extends SearchBuilder
{
    /**
     * @return QueryBuilderInterface
     */
    public function build() : QueryBuilderInterface
    {
        $this->addFilters();
        $this->addFunctionScore();
        // facet search
        $this->addAggregation();

        $this->queryBuilder->setSize(0, 4);

        return $this->queryBuilder;
    }

    public function setSort($sort)
    {
        $this->queryBuilder->addSort($sort);
    }
}
