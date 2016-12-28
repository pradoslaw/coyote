<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Forum;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Topic;
use Illuminate\Http\Request;

class RedirectIfMoved
{
    /**
     * @var ForumRepository
     */
    private $forum;

    /**
     * @var Request
     */
    private $request;

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
        $this->request = $request;

        if ($this->isUrlIncorrect()) {
            $topic = $request->route('topic');
            $forum = $this->forum->find($topic->forum_id);

            $request->route()->setParameter('forum', $forum);

            if ($request->isMethod('get')) {
                return $this->getRedirector($forum, $topic);
            }
        }

        return $next($request);
    }

    /**
     * @return bool
     */
    private function isUrlIncorrect()
    {
        $forum = $this->request->route('forum');
        $topic = $this->request->route('topic');

        if ($forum->id !== $topic->forum_id
            || ($this->request->route('slug') !== null && $this->request->route('slug') !== $topic->slug)) {
            return true;
        }

        return false;
    }

    /**
     * @param Forum|object|string $forum
     * @param Topic|object|string $topic
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    private function getRedirector(Forum $forum, Topic $topic)
    {
        switch ($this->request->route()->getName()) {
            case 'forum.topic':
                return $this->route('forum.topic', [$forum->slug, $topic->id, $topic->slug]);

            case 'forum.post.submit':
                return $this->route('forum.post.submit', [$forum->slug, $topic->id, $this->request->route('post')]);

            case 'forum.post.edit':
                return $this->route('forum.post.edit', [$forum->slug, $topic->id, $this->request->route('post')]);

            default:
                throw new \Exception('Unknown route in middleware: ' . $this->request->route()->getName());
        }
    }

    /**
     * @param string $route
     * @param array $arguments
     * @return \Illuminate\Http\RedirectResponse
     */
    private function route($route, array $arguments)
    {
        return redirect()->route($route, array_merge($arguments, $this->request->query()), 301);
    }
}
