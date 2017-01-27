<?php

namespace Coyote\Services\Elasticsearch;

class QueryBuilder implements QueryBuilderInterface
{
    const MUST = 'must';
    const MUST_NOT = 'must_not';
    const SHOULD = 'should';

    /**
     * Default query builder array
     *
     * @var array
     */
    protected $body = [
        'query' => [
            'bool' => [
                'must' => [],
                'must_not' => [],
                'should' => [],
                'filter' => []
            ]
        ],

        'sort' => [],
        'highlight' => [
            'pre_tags' => ['<em class="highlight">'],
            'post_tags' => ["</em>"],
            'fields' => []
        ],

    ];

    /**
     * @var DslInterface[]
     */
    protected $stock = [];

    /**
     * @var DslInterface[]
     */
    protected $bool = [];

    /**
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param DslInterface $bool
     * @return $this
     */
    public function should(DslInterface $bool)
    {
        $this->bool[self::SHOULD][] = $bool;

        return $this;
    }

    /**
     * @param DslInterface $bool
     * @return $this
     */
    public function must(DslInterface $bool)
    {
        $this->bool[self::MUST][] = $bool;

        return $this;
    }

    /**
     * @param DslInterface $bool
     * @return $this
     */
    public function mustNot(DslInterface $bool)
    {
        $this->bool[self::MUST_NOT][] = $bool;

        return $this;
    }

    /**
     * @param DslInterface $sort
     * @return $this|QueryBuilder
     */
    public function sort(DslInterface $sort)
    {
        return $this->addToStock($sort);
    }

    /**
     * @param DslInterface $aggs
     * @return QueryBuilder
     */
    public function aggs(DslInterface $aggs)
    {
        return $this->addToStock($aggs);
    }

    /**
     * @param DslInterface $highlight
     * @return QueryBuilder
     */
    public function highlight(DslInterface $highlight)
    {
        return $this->addToStock($highlight);
    }

    /**
     * @param int $from
     * @param int $size
     * @return $this
     */
    public function size($from, $size)
    {
        $this->body['from'] = $from;
        $this->body['size'] = $size;

        return $this;
    }

    /**
     * @param DslInterface $function
     * @return QueryBuilder
     */
    public function score(DslInterface $function)
    {
        return $this->addToStock($function);
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function source(array $fields)
    {
        $this->body['_source'] = $fields;

        return $this;
    }

    /**
     * @return array
     */
    public function build()
    {
        foreach ($this->bool as $context => $stock) {
            foreach ($stock as $item) {
                /** @var DslInterface $item */
                $this->body['query']['bool'][$context][] = $item->apply($this);
            }
        }

        foreach ($this->stock as $stock) {
            $this->body = $stock->apply($this);
        }

        return $this->body;
    }

    /**
     * @param DslInterface $stock
     * @return $this
     */
    protected function addToStock(DslInterface $stock)
    {
        $this->stock[] = $stock;

        return $this;
    }
}
