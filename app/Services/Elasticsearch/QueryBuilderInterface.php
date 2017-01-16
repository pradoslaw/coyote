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
    public function sort(DslInterface $sort);

    /**
     * @param DslInterface $aggs
     * @return QueryBuilder
     */
    public function addAggs(DslInterface $aggs);

    /**
     * @param DslInterface $highlight
     * @return QueryBuilder
     */
    public function highlight(DslInterface $highlight);

    /**
     * @param int $from
     * @param int $size
     * @return $this
     */
    public function size($from, $size);

    /**
     * @return array
     */
    public function build();
}
