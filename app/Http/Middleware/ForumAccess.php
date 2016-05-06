<?php

namespace Coyote\Http\Middleware;

use Closure;

class ForumAccess extends AbstractMiddleware
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
        $forum = $request->route('forum');
        $hasAccess = $forum->userCanAccess($request->user() ? $request->user()->id : null);

        if (!$hasAccess) {
            return $this->unauthorized($request);
        }

        return $next($request);
    }
}
