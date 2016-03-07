<?php

namespace Coyote\Http\Middleware;

use Closure;

class VerifyJobSession
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
        if (!$request->session()->has('job')) {
            return redirect()->route('job.submit')->with('error', 'Przepraszamy, ale Twoja sesja wygasła po conajmniej 15 minutach nieaktywności.');
        }

        return $next($request);
    }
}
