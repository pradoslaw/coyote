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
            return (object) [];
        }

        return [static::FILTER_NAME => [$this->field => $this->value]];
    }
}
