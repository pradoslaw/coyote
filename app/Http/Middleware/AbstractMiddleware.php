<?php

namespace Coyote\Http\Middleware;

use Illuminate\Http\Request;

abstract class AbstractMiddleware
{
    /**
     * @param Request $request
     * @return mixed
     */
    protected function unauthorized(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        } else {
            abort(401, 'Unauthorized');
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function login(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        } else {
            return redirect()->guest(route('login'));
        }
    }
}
