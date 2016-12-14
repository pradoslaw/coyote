<?php

namespace Coyote\Services\Elasticsearch;

class Query implements DslInterface
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
     * Query constructor.
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
        $body = $queryBuilder->getBody();

        $body['query']['filtered']['query'] = [
            'query_string' => [
                'query' => $this->escape($this->query),
                'fields' => $this->fields
            ]
        ];

        return $body;
    }

    /**
     * @param $query
     * @return mixed
     */
    protected function escape($query)
    {
        return str_replace(['/', '\:'], ['\/', ':'], preg_quote($query, '+-!{}[]^~*?\\'));
    }
}
