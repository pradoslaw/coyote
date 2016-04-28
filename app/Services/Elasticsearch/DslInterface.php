<?php

namespace Coyote\Services\Elasticsearch;

interface DslInterface
{
    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function apply(QueryBuilderInterface $queryBuilder);
}
