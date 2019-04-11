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
        return MicroblogResource::collection(Microblog::with('children')->paginate());
    }
}
