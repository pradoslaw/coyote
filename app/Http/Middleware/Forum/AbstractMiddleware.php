<?php

namespace Coyote\Http\Middleware\Forum;

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
        }

        abort(401, 'Unauthorized');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function login(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirect(Request $request)
    {
        return redirect()->route(
            $request->route()->getName(),
            array_merge($request->route()->parameters(), $request->query()),
            301
        );
    }
}
