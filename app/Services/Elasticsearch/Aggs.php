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
     * @var int
     */
    protected $size;

    /**
     * Aggs constructor.
     * @param string $name
     * @param string $field
     * @param int $size
     */
    public function __construct($name, $field, $size = 15)
    {
        $this->name = $name;
        $this->field = $field;
        $this->size = $size;
    }
}
