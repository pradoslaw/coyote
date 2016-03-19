<?php

namespace Coyote\Elasticsearch\Aggs\Job;

use Coyote\Elasticsearch\Aggs;
use Coyote\Elasticsearch\DslInterface;

class Location extends Aggs\Nested implements DslInterface
{
    public function __construct()
    {
        parent::__construct('locations', 'city');
    }
}