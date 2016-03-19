<?php

namespace Coyote\Elasticsearch\Filters;

use Coyote\Elasticsearch\Dsl;
use Coyote\Elasticsearch\Filter;

class Term extends Filter implements Dsl
{
    const FILTER_NAME = 'term';
}