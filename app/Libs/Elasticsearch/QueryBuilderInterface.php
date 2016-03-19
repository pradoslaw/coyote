<?php

namespace Coyote\Elasticsearch;

interface QueryBuilderInterface
{
    /**
     * @return array
     */
    public function getBody();

    /**
     * @param Dsl $query
     * @return $this|QueryBuilder
     */
    public function addQuery(Dsl $query);

    /**
     * @param Dsl $filter
     * @return $this|QueryBuilder
     */
    public function addFilter(Dsl $filter);

    /**
     * @param Dsl $sort
     * @return $this|QueryBuilder
     */
    public function addSort(Dsl $sort);

    /**
     * @return array
     */
    public function build();
}
