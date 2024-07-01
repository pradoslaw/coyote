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
        /** @var Flags $flags */
        $flags = resolve(Flags::class);
        $resourceFlags = $flags->fromModels([Microblog::class])
            ->permission('microblog-delete')
            ->get();
        return FlagResource::collection($resourceFlags)->toArray($this->request);
    }
}
