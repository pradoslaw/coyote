<?php

namespace Coyote\Services\Elasticsearch;

class QueryBuilder implements QueryBuilderInterface
{
    /**
     * Default query builder array
     *
     * @var array
     */
    protected $body = [
        'query' => [
            'filtered' => [
                'query' => [
                    'match_all' => []
                ],
                'filter' => [
                    'and' => [
                        'filters' => [
                            [
                                'or' => [
                                    'filters' => []
                                ]
                            ]
                        ]
                    ]
                ]
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
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param DslInterface $query
     * @return $this|QueryBuilder
     */
    public function addQuery(DslInterface $query)
    {
        return $this->addToStock($query);
    }

    /**
     * @param DslInterface $filter
     * @return $this|QueryBuilder
     */
    public function addFilter(DslInterface $filter)
    {
        return $this->addToStock($filter);
    }

    /**
     * @param DslInterface $sort
     * @return $this|QueryBuilder
     */
    public function addSort(DslInterface $sort)
    {
        return $this->addToStock($sort);
    }

    /**
     * @param DslInterface $aggs
     * @return QueryBuilder
     */
    public function addAggs(DslInterface $aggs)
    {
        return $this->addToStock($aggs);
    }

    /**
     * @param DslInterface $highlight
     * @return QueryBuilder
     */
    public function addHighlight(DslInterface $highlight)
    {
        return $this->addToStock($highlight);
    }

    /**
     * @param int $from
     * @param int $size
     */
    public function setSize($from, $size)
    {
        $this->body['from'] = $from;
        $this->body['size'] = $size;
    }

    /**
     * @return array
     */
    public function build()
    {
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