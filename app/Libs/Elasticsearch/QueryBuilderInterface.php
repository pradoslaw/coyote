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
     * @param Dsl $aggs
     * @return QueryBuilder
     */
    public function addAggs(Dsl $aggs);

    /**
     * @param Dsl $highlight
     * @return QueryBuilder
     */
    public function addHighlight(Dsl $highlight);

    /**
     * @return array
     */
    public function build();
}
