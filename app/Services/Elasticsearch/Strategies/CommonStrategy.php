<?php

namespace Coyote\Services\Elasticsearch\Strategies;

use Coyote\Services\Elasticsearch\Api;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CommonStrategy implements StrategyInterface
{
    /**
     * @var Api
     */
    protected $api;

    public function setApi(Api $api): void
    {
        $this->api = $api;
    }

    public function search(Request $request)
    {
        $hits = $this->api->search($request->input('q'));

        $paginator = new LengthAwarePaginator($hits->hits, $hits->total, 10);

        return $paginator->toJson();
    }
}
