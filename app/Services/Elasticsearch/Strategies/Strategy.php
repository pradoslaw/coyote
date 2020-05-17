<?php

namespace Coyote\Services\Elasticsearch\Strategies;

use Coyote\Services\Elasticsearch\Api;

abstract class Strategy implements StrategyInterface
{
    /**
     * @var Api
     */
    protected $api;

    public function setApi(Api $api): void
    {
        $this->api = $api;
    }
}
