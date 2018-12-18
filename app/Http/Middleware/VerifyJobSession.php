<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Job;
use Coyote\Services\Job\Draft;
use Illuminate\Http\Request;

class VerifyJobSession
{
    /**
     * @var Draft
     */
    private $draft;

    /**
     * @param Draft $draft
     */
    public function __construct(Draft $draft)
    {
        $this->draft = $draft;
    }

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
        if ($this->draft->has(Job::class)) {
            return $next($request);
        }

        return redirect()
            ->route('job.submit')
            ->with('error', 'Przepraszamy, ale Twoja sesja wygasła po conajmniej 15 minutach nieaktywności.');
    }
}
