<?php

namespace Coyote\Services\Elasticsearch\Strategies;

use Coyote\Http\Resources\HitResource;
use Coyote\Services\Elasticsearch\Api;
use Coyote\Services\Elasticsearch\Hits;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

    protected function rawResponse(Hits $hits, Request $request)
    {
        $paginator = new LengthAwarePaginator($hits->hits, $hits->total, 10);

        return HitResource::collection($paginator)->additional(['took' => $hits->took])->toResponse($request);
    }
}
