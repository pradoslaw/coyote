<?php

namespace Coyote\Http\Controllers\Api;

use Illuminate\Routing\Controller;

class PermanentRedirectController extends Controller
{
    /**
     * @param $any
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirect($any)
    {
        return redirect($any, 301);
    }
}
