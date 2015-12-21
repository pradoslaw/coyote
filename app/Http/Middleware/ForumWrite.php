<?php

namespace Coyote\Http\Middleware;

use Closure;
use Gate;

class ForumWrite
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

        if (auth()->guest() && !$forum->enable_anonymous) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest(route('login'));
            }
        }

        if ($forum->is_locked && !Gate::allows('update', $forum)) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                abort(401, 'Unauthorized');
            }
        }

        return $next($request);
    }
}
