<?php

namespace Coyote\Services\Elasticsearch;

interface QueryBuilderInterface
{
    /**
     * @return array
     */
    public function getBody();

    /**
     * @param DslInterface $bool
     * @return $this
     */
    public function should(DslInterface $bool);

    /**
     * @param DslInterface $bool
     * @return $this
     */
    public function must(DslInterface $bool);

    /**
     * @param DslInterface $bool
     * @return $this
     */
    public function mustNot(DslInterface $bool);

    /**
     * @param DslInterface $sort
     * @return $this|QueryBuilder
     */
    public function sort(DslInterface $sort);

    /**
     * @param DslInterface $aggs
     * @return QueryBuilder
     */
    public function aggs(DslInterface $aggs);

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
     * @param DslInterface $function
     * @return QueryBuilder
     */
    public function score(DslInterface $function);

    /**
     * @return array
     */
    public function build();
}
