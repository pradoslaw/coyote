<?php
namespace Coyote\Http\Middleware\Forum;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation;

abstract class AbstractMiddleware
{
    protected function unauthorized(Request $request): HttpFoundation\Response
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        }
        abort(401, 'Unauthorized');
    }

    protected function login(Request $request): HttpFoundation\Response
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        }
        return redirect()->guest(route('login'));
    }

    protected function redirect(Request $request): RedirectResponse
    {
        return redirect()->route(
            $request->route()->getName(),
            array_merge($request->route()->parameters(), $request->query()),
            301
        );
    }
}
