<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForumAccess extends AbstractMiddleware
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
        $forum = $request->route('forum');
        $hasAccess = $forum->userCanAccess($request->user() ? $request->user()->id : null);

        if (!$hasAccess) {
            return $this->unauthorized($request);
        }

        return $next($request);
    }
}
