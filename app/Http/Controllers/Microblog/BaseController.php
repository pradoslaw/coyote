<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\FlagFactory;
use Coyote\Http\Resources\FlagResource;
use Coyote\Microblog;

class BaseController extends Controller
{
    use FlagFactory;

    /**
     * @return array
     */
    protected function flags(): array
    {
        if (!$this->userId || !$this->auth->can('microblog-delete')) {
            return [];
        }

        $repository = $this->getFlagFactory();
        $flags = $repository->findAllByModel(Microblog::class);

        return FlagResource::collection($flags)->toArray($this->request);
    }
}
