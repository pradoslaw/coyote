<?php

namespace Coyote\Services\Elasticsearch;

interface QueryBuilderInterface
{
    /**
     * @return array
     */
    public function getBody();

    /**
     * @param DslInterface $query
     * @return $this|QueryBuilder
     */
    public function addQuery(DslInterface $query);

    /**
     * @param DslInterface $filter
     * @return $this|QueryBuilder
     */
    public function addFilter(DslInterface $filter);

    /**
     * @param DslInterface $sort
     * @return $this|QueryBuilder
     */
    public function addSort(DslInterface $sort);

    /**
     * @param DslInterface $aggs
     * @return QueryBuilder
     */
    public function addAggs(DslInterface $aggs);

    /**
     * @param DslInterface $highlight
     * @return QueryBuilder
     */
    public function addHighlight(DslInterface $highlight);

    /**
     * @param int $from
     * @param int $size
     */
    public function setSize($from, $size);

    /**
     * @return array
     */
    public function build();
}
