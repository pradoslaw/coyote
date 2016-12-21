<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var \Coyote\Forum $forum */
        $forum = $request->route('forum');

        if (!empty($forum->url)) {
            $forum->redirects++;
            $forum->save();

            return redirect()->away($forum->url);
        }

        return $next($request);
    }
}
