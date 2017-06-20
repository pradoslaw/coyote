<?php

namespace Coyote\Services\Elasticsearch\Aggs\Job;

use Coyote\Services\Elasticsearch\Aggs;
use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Remote extends Aggs\Terms implements DslInterface
{
    use GlobalAggregationTrait;

    public function __construct()
    {
        parent::__construct('remote', 'remote_range');
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
