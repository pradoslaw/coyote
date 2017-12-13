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
        // everything is ok if there is a model in session...
        if ($request->session()->has(Job::class)) {
            $model = $request->session()->get(Job::class);

            // ...to be sure we have to check if it's really a model...
            if ($model instanceof Job) {
                // title MOST NOT be empty (except postIndex() method)
                if ($request->route()->getActionMethod() === 'postIndex' || $model->title) {
                    return $next($request);
                }
            }
        }

        return redirect()
            ->route('job.submit')
            ->with('error', 'Przepraszamy, ale Twoja sesja wygasła po conajmniej 15 minutach nieaktywności.');
    }
}
