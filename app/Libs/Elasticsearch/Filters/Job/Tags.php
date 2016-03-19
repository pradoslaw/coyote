<?php

namespace Coyote\Elasticsearch\Filters\Job;

use Coyote\Elasticsearch\Dsl;
use Coyote\Elasticsearch\Filters\Terms;

class Tags extends Terms implements Dsl
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