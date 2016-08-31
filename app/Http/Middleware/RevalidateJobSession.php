<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RevalidateJobSession
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
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
     * @param Request  $request
     */
    private function removeSession(Request $request)
    {
        $request->session()->remove('job');
        $request->session()->remove('firm');
    }
}
