<?php

namespace Coyote\Elasticsearch\Filters;

use Coyote\Elasticsearch\DslInterface;
use Coyote\Elasticsearch\Filter;

class Term extends Filter implements DslInterface
{
    const FILTER_NAME = 'term';
}