<?php

namespace Coyote\Http\Middleware;

use Closure;
use Gate;

class AdmAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Gate::denies('adm-access')) {
            abort(403);
        }

        return $next($request);
    }
}
