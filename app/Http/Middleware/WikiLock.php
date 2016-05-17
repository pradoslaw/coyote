<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Access\Gate;

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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->wiki->is_locked && $this->gate->denies('wiki-admin')) {
            abort(401);
        }

        return $next($request);
    }
}
