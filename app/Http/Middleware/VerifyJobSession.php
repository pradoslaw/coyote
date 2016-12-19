<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyJobSession
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
        if (!$request->session()->has('job.title')) {
            return redirect()
                ->route('job.submit')
                ->with('error', 'Przepraszamy, ale Twoja sesja wygasła po conajmniej 15 minutach nieaktywności.');
        }

        return $next($request);
    }
}
