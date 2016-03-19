<?php

namespace Coyote\Elasticsearch;

class Highlight implements Dsl
{
    /**
     * @var array
     */
    protected $fields = [];

    /**
     * Highlight constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        foreach ($fields as $field) {
            $this->fields[$field] = (object) [];
        }
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = $queryBuilder->getBody();
        $body['highlight']['fields'] = $this->fields;

        return $body;
    }
}
