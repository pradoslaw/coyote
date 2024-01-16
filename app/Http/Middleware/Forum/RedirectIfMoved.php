<?php
namespace Coyote\Http\Middleware\Forum;

use Closure;
use Coyote\Repositories\Eloquent\ForumRepository;
use Coyote\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation;

class RedirectIfMoved extends AbstractMiddleware
{
    public function __construct(private ForumRepository $forum)
    {
    }

    public function handle(Request $request, Closure $next): HttpFoundation\Response
    {
        // check if url is invalid if category was changed or slug was changed
        if (!$this->isInvalidUrl($request)) {
            return $next($request);
        }

        /** @var Topic $topic */
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

    private function replaceParameter(Request $request, string $name, $value)
    {
        if ($request->route()->hasParameter($name)) {
            $request->route()->setParameter($name, $value);
        }
    }

    private function isInvalidUrl(Request $request): bool
    {
        $forum = $request->route('forum');
        $topic = $request->route('topic');

        return (is_null($forum)
            || $forum->id !== $topic->forum_id
            || ($request->route('slug') !== null && $request->route('slug') !== $topic->slug));
    }

    private function redirect(Request $request): RedirectResponse
    {
        return redirect()->route(
            $request->route()->getName(),
            \array_merge($request->route()->parameters(), $request->query()),
            status:301);
    }
}
