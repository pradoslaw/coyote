<?php

namespace Coyote\Elasticsearch;

interface Dsl
{
    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function apply(QueryBuilderInterface $queryBuilder);
}