<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Services\Job\Draft;
use Illuminate\Http\Request;

class ForgetJobDraft
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
        if ($request->has('revalidate')) {
            $this->draft->forget();
        }

        return $next($request);
    }
}
