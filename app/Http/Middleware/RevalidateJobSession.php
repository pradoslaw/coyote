<?php

namespace Coyote\Http\Middleware;

use Closure;

class RevalidateJobSession
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
        if ($request->has('revalidate')) {
            $request->session()->remove('job');
            $request->session()->remove('firm');
        }

        return $next($request);
    }
}
