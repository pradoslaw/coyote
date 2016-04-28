<?php

namespace Coyote\Services\Elasticsearch\Filters;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filter;

class Term extends Filter implements DslInterface
{
    const FILTER_NAME = 'term';
}
