<?php

namespace Coyote\Elasticsearch\Filters;

use Coyote\Elasticsearch\DslInterface;
use Coyote\Elasticsearch\Filter;

class Range extends Filter implements DslInterface
{
    const FILTER_NAME = 'range';
}