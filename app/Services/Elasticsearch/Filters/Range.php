<?php

namespace Coyote\Services\Elasticsearch\Filters;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filter;

class Range extends Filter implements DslInterface
{
    const FILTER_NAME = 'range';
}
