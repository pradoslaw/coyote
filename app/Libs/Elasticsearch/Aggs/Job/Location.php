<?php

namespace Coyote\Elasticsearch\Aggs\Job;

use Coyote\Elasticsearch\Aggs;
use Coyote\Elasticsearch\Dsl;

class Location extends Aggs\Nested implements Dsl
{
    public function __construct()
    {
        parent::__construct('locations', 'city');
    }
}