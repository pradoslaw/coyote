<?php

namespace Coyote\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PermanentRedirectController extends Controller
{
    /**
     * @param string $path
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirect($path, Request $request)
    {
        return redirect($path . ($request->getQueryString() ? ('?' . $request->getQueryString()) : ''), 301);
    }
}
