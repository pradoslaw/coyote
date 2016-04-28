<?php

namespace Coyote\Services\Elasticsearch;

abstract class Aggs implements DslInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $field;

    /**
     * Aggs constructor.
     * @param string $name
     * @param string $field
     */
    public function __construct($name, $field)
    {
        $this->name = $name;
        $this->field = $field;
    }
}
