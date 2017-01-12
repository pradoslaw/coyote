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

        // case sensitive redirection. redirect to the original url if needed.
        if ($this->isInvalidUrl() && $request->isMethod('get')) {
            return redirect()->route(
                $request->route()->getName(),
                array_merge($request->route()->parameters(), $request->query()),
                301
            );
        }

        if (!$forum->userCanAccess($request->user() ? $request->user()->id : null)) {
            return $this->unauthorized($request);
        }

        return $next($request);
    }

    /**
     * @return bool
     */
    private function isInvalidUrl()
    {
        list(, $name, ) = explode('/', trim($this->request->getPathInfo(), '/'));

        if ($name !== $this->request->route('forum')->slug) {
            return true;
        }

        return false;
    }
}
