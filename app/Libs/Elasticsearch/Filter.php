<?php

namespace Coyote\Elasticsearch;

abstract class Filter implements DslInterface
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * Filter constructor.
     * @param string $field
     * @param mixed $value
     */
    public function __construct($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @param $filter
     * @return mixed
     */
    public function addFilter(QueryBuilderInterface $queryBuilder, $filter)
    {
        $filters = array_get($queryBuilder->getBody(), 'query.filtered.filter.and.filters');
        $filters[] = $filter;

        $body = $queryBuilder->getBody();
        $body['query']['filtered']['filter']['and']['filters'] = $filters;

        return $body;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        return $this->addFilter($queryBuilder, [static::FILTER_NAME => [$this->field => $this->value]]);
    }
}