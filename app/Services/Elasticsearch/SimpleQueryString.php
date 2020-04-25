<?php

namespace Coyote\Services\Elasticsearch;

class SimpleQueryString implements DslInterface
{
    /**
     * @var string
     */
    protected $query;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var int
     */
    protected $boost;

    /**
     * @param string $query
     * @param array $fields
     * @param int $boost
     */
    public function __construct($query, $fields, $boost = 1)
    {
        $this->query = $query;
        $this->fields = $fields;
        $this->boost = $boost;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        return [
            'simple_query_string' => [
                'query'     => $this->query,
                'fields'    => $this->fields,
                'boost'     => $this->boost
            ]
        ];
    }
}
