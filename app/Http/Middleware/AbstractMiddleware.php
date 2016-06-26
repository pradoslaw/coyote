<?php

namespace Coyote\Http\Middleware;

abstract class AbstractMiddleware
{
    /**
     * @param \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function unauthorized($request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        } else {
            abort(401, 'Unauthorized');
        }
    }

    /**
     * @param \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function login($request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        } else {
            return redirect()->guest(route('login'));
        }
    }
}
