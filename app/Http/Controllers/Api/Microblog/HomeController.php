<?php

namespace Coyote\Http\Controllers\Api\Microblog;

use Coyote\Http\Resources\MicroblogResource;
use Coyote\Microblog;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $data = Microblog::with(['children.user:id,name,photo', 'user:id,name,photo'])->whereNull('parent_id')->orderBy('id', 'DESC')->paginate();

        return MicroblogResource::collection($data);
    }
}
