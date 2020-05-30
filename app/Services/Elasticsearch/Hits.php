<?php

namespace Coyote\Services\Elasticsearch;

class Hits
{
    /**
     * @var int
     */
    public $total;

    /**
     * @var int
     */
    public $took;

    /**
     * @var array
     */
    public $hits = [];

    public function __construct(array $hits = [], int $took = 0, int $total = 0)
    {
        $this->hits = $hits;
        $this->took = $took;
        $this->total = $total;
    }
}
