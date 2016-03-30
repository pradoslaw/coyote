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
        if ($request->has('revalidate')
            || ($request->session()->has('job')
                && $request->route('id') !== null
                && $request->session()->get('job.id') !== $request->route('id'))
        ) {
            $this->removeSession($request);
        }

        return $next($request);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    private function removeSession($request)
    {
        $request->session()->remove('job');
        $request->session()->remove('firm');
    }
}
