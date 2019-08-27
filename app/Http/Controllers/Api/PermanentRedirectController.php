<?php

namespace Coyote\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PermanentRedirectController extends Controller
{
    /**
     * @param $any
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirect($any, Request $request)
    {
        return redirect($any . ($request->getQueryString() ? ('?' . $request->getQueryString()) : ''), 301);
    }
}
