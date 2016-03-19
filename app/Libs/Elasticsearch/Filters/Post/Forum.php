<?php

namespace Coyote\Elasticsearch\Filters\Post;

use Coyote\Elasticsearch\DslInterface;
use Coyote\Elasticsearch\Filters\Terms;

class Forum extends Terms implements DslInterface
{
    /**
     * @var array
     */
    protected $forumsId = [];

    /**
     * Forum constructor.
     * @param int|array $forumsId
     */
    public function __construct($forumsId)
    {
        if (!is_array($forumsId)) {
            $forumsId = [$forumsId];
        }

        parent::__construct('forum_id', $forumsId);
    }
}