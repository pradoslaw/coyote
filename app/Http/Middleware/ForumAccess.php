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
//        $groupsId = $forum->access()->lists('group_id');

        return $next($request);
    }
}
