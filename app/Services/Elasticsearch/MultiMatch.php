<?php

namespace Coyote\Services\Elasticsearch;

class MultiMatch implements DslInterface
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
     * @param string $query
     * @param array $fields
     */
    public function __construct($query, $fields)
    {
        $this->query = $query;
        $this->fields = $fields;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        return [
            'multi_match' => [
                'query'     => $this->query,
                'fields'    => $this->fields
            ]
        ];
    }
}
