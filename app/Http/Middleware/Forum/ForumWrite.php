<?php
namespace Coyote\Http\Middleware\Forum;

use Closure;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation;

class ForumWrite extends AbstractMiddleware
{
    public function __construct(private Gate $gate)
    {
    }

    public function handle(Request $request, Closure $next): HttpFoundation\Response
    {
        $forum = $request->route('forum');

        // redirect to login page instead of throwing exception
        if (!$forum->enable_anonymous && empty($request->user())) {
            return $this->login($request);
        }

        if ($this->gate->denies('write', $forum)) {
            return $this->unauthorized($request);
        }

        $topic = $request->route('topic');
        if (!empty($topic) && $this->gate->denies('write', $topic)) {
            return $this->unauthorized($request);
        }

        return $next($request);
    }
}
