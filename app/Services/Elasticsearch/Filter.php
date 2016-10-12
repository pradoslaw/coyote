<?php

namespace Coyote\Services\Elasticsearch;

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
     * @return mixed
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        if (empty($this->value)) {
            return $queryBuilder->getBody();
        }

        return $this->addFilter($queryBuilder, [static::FILTER_NAME => [$this->field => $this->value]]);
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @param $filter
     * @return mixed
     */
    protected function addFilter(QueryBuilderInterface $queryBuilder, $filter)
    {
        $filters = array_get($queryBuilder->getBody(), 'query.filtered.filter.and.filters');
        $filters[] = $filter;

        $body = $queryBuilder->getBody();
        $body['query']['filtered']['filter']['and']['filters'] = $filters;

        return $body;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @param $filter
     * @return array
     *
     * @todo potrzebny refaktoring poniewaz kod tej metody dubluje sie z poprzednim (DRY!)
     */
    protected function addOrFilter(QueryBuilderInterface $queryBuilder, $filter)
    {
        $filters = array_get($queryBuilder->getBody(), 'query.filtered.filter.and.filters.0.or.filters');
        $filters[] = $filter;

        $body = $queryBuilder->getBody();
        $body['query']['filtered']['filter']['and']['filters'][0]['or']['filters'] = $filters;

        return $body;
    }
}
