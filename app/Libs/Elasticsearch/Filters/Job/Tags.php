<?php

namespace Coyote\Elasticsearch\Filters\Job;

use Coyote\Elasticsearch\DslInterface;
use Coyote\Elasticsearch\Filters\Terms;

class Tags extends Terms implements DslInterface
{
    /**
     * Tags constructor.
     * @param string $tags
     */
    public function __construct($tags)
    {
        parent::__construct('tags', $tags);
    }
}