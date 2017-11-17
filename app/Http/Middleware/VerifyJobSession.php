<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Job;
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
        if ($request->session()->has(Job::class . '.title')) {
            return $next($request);
        }

        return redirect()
            ->route('job.submit')
            ->with('error', 'Przepraszamy, ale Twoja sesja wygasła po conajmniej 15 minutach nieaktywności.');
    }
}
