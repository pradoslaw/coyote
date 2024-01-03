<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;

class AdmAccess
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param bool $isLogged
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $isLogged)
    {
        /** @var Gate $gate */
        $gate = app(Gate::class);
        if ($gate->denies('adm-access')) {
            abort(401);
        }

        // admin panel sometimes requires to re-enter the password
        // if session has the key "admin", that means password has been re-entered
        // and we can show the page. otherwise we have to redirect to form where user
        // can re-enter his password
        if ($isLogged && !$request->session()->has('admin')) {
            if (!$request->session()->has('url.intended')) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('adm.home');
        }

        return $next($request);
    }
}
