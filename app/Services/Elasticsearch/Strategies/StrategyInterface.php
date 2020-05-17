<?php

namespace Coyote\Services\Elasticsearch\Strategies;

use Coyote\Services\Elasticsearch\Api;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface StrategyInterface
{
    public function setApi(Api $api): void;
    public function search(Request $request): JsonResponse;
}
