<?php

namespace Coyote\Http\Middleware;

use Closure;

class ForumAccess
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
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                abort(401, 'Unauthorized');
            }
        }

        return $next($request);
    }
}
