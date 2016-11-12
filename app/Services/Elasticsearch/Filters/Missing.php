<?php

namespace Coyote\Services\Elasticsearch\Filters;

use Coyote\Services\Elasticsearch\Filter;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Missing extends Filter
{
    /**
     * @param string $field
     * @param string $value
     */
    public function __construct($field, $value = '')
    {
        parent::__construct($field, $value);
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        return $this->addOrFilter($queryBuilder, ['missing' => ['field' => $this->field]]);
    }
}
