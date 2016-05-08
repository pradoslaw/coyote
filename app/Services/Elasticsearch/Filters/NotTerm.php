<?php

namespace Coyote\Services\Elasticsearch\Filters;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filter;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class NotTerm extends Filter implements DslInterface
{
    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        return $this->addFilter($queryBuilder, ['not' => ['term' => [$this->field => $this->value]]]);
    }
}
