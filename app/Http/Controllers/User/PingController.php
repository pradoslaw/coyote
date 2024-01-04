<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class PingController extends Controller
{
    /**
     * @deprecated
     */
    public function index(): Response
    {
        return \response(\csrf_token());
    }
}
