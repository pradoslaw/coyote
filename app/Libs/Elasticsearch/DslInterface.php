<?php

namespace Coyote\Elasticsearch;

interface DslInterface
{
    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function apply(QueryBuilderInterface $queryBuilder);
}