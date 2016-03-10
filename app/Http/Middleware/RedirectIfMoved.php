<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;

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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $forum = $request->route('forum');
        $topic = $request->route('topic');

        if ($forum->id !== $topic->forum_id
            || $request->route('slug') !== $topic->path) {
            $forum = $this->forum->find($topic->forum_id, ['path']);

            return redirect(route('forum.topic', [$forum->path, $topic->id, $topic->path]));
        }

        return $next($request);
    }
}
