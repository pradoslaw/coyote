<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;

class PingController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return response('pong');
    }
}
