<?php

namespace Coyote\Elasticsearch\Aggs\Job;

use Coyote\Elasticsearch\Aggs;
use Coyote\Elasticsearch\DslInterface;
use Coyote\Elasticsearch\QueryBuilderInterface;

class Tag extends Aggs\Terms implements DslInterface
{
    use Job;

    public function __construct()
    {
        parent::__construct('tags', 'tag_original');
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