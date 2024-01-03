<?php
namespace Coyote\Http\Middleware\Forum;

use Closure;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation;

class WikiLock extends AbstractMiddleware
{
    public function __construct(private Gate $gate)
    {
    }

    public function handle(Request $request, Closure $next): HttpFoundation\Response
    {
        if ($request->wiki->is_locked && $this->gate->denies('wiki-admin')) {
            abort(401);
        }
        return $next($request);
    }
}
