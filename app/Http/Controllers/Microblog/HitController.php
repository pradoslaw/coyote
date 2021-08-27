<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Microblog;
use Coyote\Services\UrlBuilder;

class HitController extends Controller
{
    public function index(Microblog $microblog)
    {
        // set original path for the request for middleware PageHit
        $this->request->attributes->set('path', UrlBuilder::microblog($microblog));

        return response('', 200);
    }
}
