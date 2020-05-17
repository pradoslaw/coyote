<?php

namespace Coyote\Services\Elasticsearch\Strategies;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CommonStrategy extends Strategy
{
    public function search(Request $request)
    {
        $hits = $this->api->search($request->input('q'));

        $paginator = new LengthAwarePaginator($hits->hits, $hits->total, 10);

        return $paginator->toJson();
    }
}
