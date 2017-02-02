<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Job;
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
        if ($request->has('revalidate')) {
            $this->removeSession($request);
        }

        return $next($request);
    }

    /**
     * @param Request  $request
     */
    private function removeSession(Request $request)
    {
        $request->session()->remove(Job::class);
    }
}
