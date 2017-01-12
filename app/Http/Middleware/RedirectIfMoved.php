<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Illuminate\Http\Request;

class RedirectIfMoved extends AbstractMiddleware
{
    /**
     * @var ForumRepository
     */
    private $forum;

    /**
     * @param ForumRepository $forum
     */
    public function __construct(ForumRepository $forum)
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
        // url is invalid if category was changed or slug was changed
        if ($this->isInvalidUrl($request)) {
            /** @var \Coyote\Topic $topic */
            $topic = $request->route('topic');

            // get current topic's category
            $forum = $this->forum->find($topic->forum_id);

            $request->route()->setParameter('forum', $forum);
            $request->route()->setParameter('slug', $topic->slug);

            if ($request->isMethod('get')) {
                return $this->redirect($request);
            }
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isInvalidUrl(Request $request)
    {
        $forum = $request->route('forum');
        $topic = $request->route('topic');

        if ($forum->id !== $topic->forum_id
            || ($request->route('slug') !== null && $request->route('slug') !== $topic->slug)) {
            return true;
        }

        return false;
    }
}
