<?php

namespace Coyote\Services\Elasticsearch;

class Sort implements DslInterface
{
    /**
     * @var string
     */
    protected $sort;

    /**
     * @var string
     */
    protected $order;

    /**
     * Sort constructor.
     * @param string $sort
     * @param string $order
     */
    public function __construct($sort, $order)
    {
        $this->sort = $sort;
        $this->order = $order;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = $queryBuilder->getBody();
        $body['sort'][] = [$this->sort => $this->order];

        return $body;
    }
}
