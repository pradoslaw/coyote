<?php

namespace Coyote\Services\Elasticsearch\Filters\Job;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filters\Term;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Remote extends Term implements DslInterface
{
    /**
     * Remote constructor.
     */
    public function __construct()
    {
        parent::__construct('is_remote', 1);
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
//    public function apply(QueryBuilderInterface $queryBuilder)
//    {
//        return $this->addOrFilter($queryBuilder, [static::FILTER_NAME => [$this->field => $this->value]]);
//    }
}
