<?php

namespace Coyote\Services\Elasticsearch\Aggs\Job;

use Coyote\Services\Elasticsearch\Aggs;
use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Tag extends Aggs\Terms implements DslInterface
{
    use Job;

    public function __construct()
    {
        parent::__construct('tags', 'tags.original');
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        return $this->buildGlobal(parent::apply($queryBuilder));
    }
}