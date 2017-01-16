<?php

namespace Coyote\Services\Elasticsearch\Filters\Post;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filters\Terms;

class OnlyThoseWithAccess extends Terms implements DslInterface
{
    public function __construct(array $forumsId)
    {
        parent::__construct('forum_id', $forumsId);
    }
}
