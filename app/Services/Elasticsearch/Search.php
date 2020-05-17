<?php

namespace Coyote\Services\Elasticsearch;

use Coyote\Services\Elasticsearch\Strategies\StrategyInterface;
use Illuminate\Http\Request;

class Search
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var Request
     */
    private $request;

    public function __construct(Api $api, Request $request)
    {
        $this->api = $api;
        $this->request = $request;
    }

    public function search(StrategyInterface $strategy): string
    {
        $strategy->setApi($this->api);

        return $strategy->search($this->request)->content();
    }
}
