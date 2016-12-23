<?php

namespace Coyote\Services\Elasticsearch\Filters;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filter;

class Range extends Filter implements DslInterface
{
    const FILTER_NAME = 'range';

    /**
     * Filter constructor.
     * @param string $field
     * @param mixed $value
     */
    public function __construct($field, $value)
    {
        parent::__construct($field, array_map([&$this, 'filterValue'], $value));
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function filterValue($value)
    {
        return (int) trim(str_replace(' ', '', $value));
    }
}
