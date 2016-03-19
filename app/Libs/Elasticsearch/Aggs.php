<?php

namespace Coyote\Elasticsearch;

abstract class Aggs implements Dsl
{
    protected $name;
    protected $field;

    public function __construct($name, $field)
    {
        $this->name = $name;
        $this->field = $field;
    }
}