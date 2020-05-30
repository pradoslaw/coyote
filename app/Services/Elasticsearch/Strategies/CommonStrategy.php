<?php

namespace Coyote\Services\Elasticsearch\Strategies;

use Coyote\Services\Elasticsearch\SearchOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommonStrategy extends Strategy
{
    public function search(Request $request): JsonResponse
    {
        $hits = $this->api->search(new SearchOptions($request));

        return $this->rawResponse($hits, $request);
    }
}
