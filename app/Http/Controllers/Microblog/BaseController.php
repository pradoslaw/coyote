<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Resources\FlagResource;
use Coyote\Microblog;
use Coyote\Services\Flags;

class BaseController extends Controller
{
    protected function flags(): array
    {
        $flags = $flags = resolve(Flags::class)->fromModels([Microblog::class])->permission('microblog-delete')->get();

        return FlagResource::collection($flags)->toArray($this->request);
    }
}
