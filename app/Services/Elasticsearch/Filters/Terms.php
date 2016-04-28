<?php

namespace Coyote\Services\Elasticsearch\Filters;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filter;

class Terms extends Filter implements DslInterface
{
    const FILTER_NAME = 'terms';

    /**
     * Terms constructor.
     * @param $field
     * @param $value
     */
    public function __construct($field, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        parent::__construct($field, $value);
    }
}
