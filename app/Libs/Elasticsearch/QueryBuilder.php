<?php

namespace Coyote\Elasticsearch;

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
                        'filters' => []
                    ]
                ]
            ]
        ],

        'sort' => [],
        'highlight' => [
            'pre_tags' => ['<em class="highlight">'],
            'post_tags' => ["</em>"],
            'fields' => [ ]
        ],

    ];

    /**
     * @var Dsl[]
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
     * @param Dsl $query
     * @return $this|QueryBuilder
     */
    public function addQuery(Dsl $query)
    {
        return $this->addToStock($query);
    }

    /**
     * @param Dsl $filter
     * @return $this|QueryBuilder
     */
    public function addFilter(Dsl $filter)
    {
        return $this->addToStock($filter);
    }

    /**
     * @param Dsl $sort
     * @return $this|QueryBuilder
     */
    public function addSort(Dsl $sort)
    {
        return $this->addToStock($sort);
    }

    /**
     * @param Dsl $aggs
     * @return QueryBuilder
     */
    public function addAggs(Dsl $aggs)
    {
        return $this->addToStock($aggs);
    }

    /**
     * @param Dsl $highlight
     * @return QueryBuilder
     */
    public function addHighlight(Dsl $highlight)
    {
        return $this->addToStock($highlight);
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
     * @param Dsl $stock
     * @return $this
     */
    protected function addToStock(Dsl $stock)
    {
        $this->stock[] = $stock;
        return $this;
    }
}