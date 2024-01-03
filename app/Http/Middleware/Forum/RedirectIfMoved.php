<?php

namespace Coyote\Http\Middleware\Forum;

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
        // check if url is invalid if category was changed or slug was changed
        if (!$this->isInvalidUrl($request)) {
            return $next($request);
        }

        /** @var \Coyote\Topic $topic */
        $topic = $request->route('topic');

        // get current topic's category
        $forum = $this->forum->find($topic->forum_id);

        // replace original route parameters with new ones
        $this->replaceParameter($request, 'forum', $forum);
        $this->replaceParameter($request, 'topic', $topic);
        $this->replaceParameter($request, 'slug', $topic->slug);

        // if this is GET request, simply redirect
        if ($request->isMethod('get')) {
            return $this->redirect($request);
        }

        return $next($request);
    }

    /**
     * Replace parameter only if it exists in URL! Otherwise request will be broken
     *
     * @param Request $request
     * @param string $name
     * @param $value
     */
    private function replaceParameter(Request $request, string $name, $value)
    {
        if ($request->route()->hasParameter($name)) {
            $request->route()->setParameter($name, $value);
        }
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isInvalidUrl(Request $request)
    {
        $forum = $request->route('forum');
        $topic = $request->route('topic');

        return (is_null($forum)
            || $forum->id !== $topic->forum_id
                || ($request->route('slug') !== null && $request->route('slug') !== $topic->slug));
    }
}
