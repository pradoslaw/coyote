<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\FlagFactory;
use Coyote\Http\Resources\FlagResource;
use Coyote\Microblog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BaseController extends Controller
{
    use FlagFactory;

    protected function flags($paginator): array
    {
        if (!$this->userId || !$this->auth->can('microblog-delete')) {
            return [];
        }

        $paginator->load('flags');

        if ($paginator instanceof LengthAwarePaginator) {
            $flags = $paginator->pluck('flags')->values()->flatten();
        } else {
            $flags = $paginator->flags;
        }

        return FlagResource::collection($flags)->toArray($this->request);
    }
}
