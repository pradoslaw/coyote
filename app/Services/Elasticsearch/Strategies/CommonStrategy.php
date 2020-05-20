<?php

namespace Coyote\Services\Elasticsearch\Strategies;

use Coyote\Http\Resources\HitResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CommonStrategy extends Strategy
{
    public function search(Request $request): JsonResponse
    {
        $hits = $this->api->search($request->input('q'), null, $request->input('sort'));

        $paginator = new LengthAwarePaginator($hits->hits, $hits->total, 10);

        return HitResource::collection($paginator)->additional(['took' => $hits->took])->toResponse($request);
    }
}
