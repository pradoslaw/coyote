<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForumAccess extends AbstractMiddleware
{
    /**
     * @var Request
     */
    private $request;

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->request = $request;

        /** @var \Coyote\Forum $forum */
        $forum = $request->route('forum');

        if (!$forum->userCanAccess($request->user() ? $request->user()->id : null)) {
            return $this->unauthorized($request);
        }

        return $next($request);
    }
}
