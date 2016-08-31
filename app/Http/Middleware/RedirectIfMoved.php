<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Illuminate\Http\Request;

class RedirectIfMoved
{
    /**
     * @var Forum
     */
    private $forum;

    /**
     * @param Forum $forum
     */
    public function __construct(Forum $forum)
    {
        $this->forum = $forum;
    }

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
        $topic = $request->route('topic');

        if ($forum->id !== $topic->forum_id
            || ($request->route('slug') !== null && $request->route('slug') !== $topic->slug)) {
            $forum = $this->forum->find($topic->forum_id, ['slug']);

            return redirect(route('forum.topic', [$forum->slug, $topic->id, $topic->slug]));
        }

        return $next($request);
    }
}
