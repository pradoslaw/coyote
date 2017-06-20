<?php

namespace Coyote\Services\Elasticsearch\Aggs\Job;

use Coyote\Services\Elasticsearch\Aggs;
use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Location extends Aggs\Nested implements DslInterface
{
    use GlobalAggregationTrait;

    public function __construct()
    {
        parent::__construct('locations', 'locations.city.original');
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        return $this->wrapGlobal(parent::apply($queryBuilder));
    }
}
