<?php

namespace Coyote\Http\Middleware\Forum;

use Closure;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;

class WikiLock extends AbstractMiddleware
{
    /**
     * @var Gate
     */
    protected $gate;

    /**
     * @param Gate $gate
     */
    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
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
        if ($request->wiki->is_locked && $this->gate->denies('wiki-admin')) {
            abort(401);
        }

        return $next($request);
    }
}
