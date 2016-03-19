<?php

namespace Coyote\Elasticsearch\Filters\Post;

use Coyote\Elasticsearch\Dsl;
use Coyote\Elasticsearch\Filters\Terms;

class Forum extends Terms implements Dsl
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