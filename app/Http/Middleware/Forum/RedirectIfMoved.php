<?php
namespace Coyote\Http\Middleware\Forum;

use Closure;
use Coyote\Forum;
use Coyote\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation;

class RedirectIfMoved extends AbstractMiddleware
{
    public function handle(Request $request, Closure $next): HttpFoundation\Response
    {
        /** @var Forum $forum */
        $forum = $request->route('forum');
        /** @var Topic $topic */
        $topic = $request->route('topic');
        /** @var string|null $slug */
        $slug = $request->route('slug') ?? '';

        return $this->handleRedirect($forum, $topic, $slug, $request, $next);
    }

    private function handleRedirect(
        Forum    $forum,
        Topic    $topic,
        string   $slug,
        Request  $request,
        callable $next): HttpFoundation\Response
    {
        $route = $request->route();
        if ($this->isCanonicalUrl($route, $forum, $topic, $slug)) {
            return $next($request);
        }
        return $this->redirectToCanonical($route, $topic, $request);
    }

    private function isCanonicalUrl(Route $route, Forum $forum, Topic $topic, string $slug): bool
    {
        return !$this->mismatchedArguments($route, $forum, $topic, $slug);
    }

    private function mismatchedArguments(Route $route, Forum $forum, Topic $topic, string $slug): bool
    {
        return $this->mismatchedCategory($topic, $forum)
            || $this->mismatchedTopicSlug($route, $topic, $slug);
    }

    private function mismatchedCategory(Topic $topic, Forum $forum): bool
    {
        return $topic->forum->id !== $forum->id;
    }

    private function mismatchedTopicSlug(Route $route, Topic $topic, ?string $slug): bool
    {
        if ($this->routeHasArgument($route, 'slug')) {
            return $topic->slug !== $slug;
        }
        return false;
    }

    private function redirectToCanonical(Route $route, Topic $topic, Request $request): RedirectResponse
    {
        $route->setParameter('forum', $topic->forum);
        if ($this->routeHasArgument($route, 'slug')) {
            $route->setParameter('slug', $topic->slug);
        }
        return $this->redirect($request);
    }

    private function routeHasArgument(Route $route, string $argument): bool
    {
        return \in_array($argument, $route->parameterNames());
    }

    private function redirect(Request $request): RedirectResponse
    {
        return redirect()->route(
            $request->route()->getName(),
            $request->route()->parameters() + $request->query(),
            $this->redirectionStatusCode($request));
    }

    private function redirectionStatusCode(Request $request): int
    {
        if ($request->isMethod('GET')) {
            return 301;
        }
        return 308;
    }
}
