<?php

namespace Coyote\Services\Elasticsearch\Filters\Post;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filters\Terms;

class OnlyThoseWithAccess extends Terms implements DslInterface
{
    public function __construct($forumId)
    {
        if (!is_array($forumId)) {
            $forumId = [$forumId]; // make array
        }

        parent::__construct('forum_id', $forumId);
    }
}
