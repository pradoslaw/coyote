<?php
namespace Coyote\Http\Middleware;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation;

class RemoveTrailingSlash
{
    public function handle(Request $request, callable $next): HttpFoundation\Response
    {
        if ($this->hasTrailingSlash($request)) {
            return $this->redirectToCanonical($request);
        }
        return $next($request);
    }

    private function hasTrailingSlash(Request $request): bool
    {
        if ($request->getRequestUri() === '/') {
            return false;
        }
        return \str_ends_with($request->getUri(), '/');
    }

    private function redirectToCanonical(Request $request): RedirectResponse
    {
        return redirect()->away(
            $this->canonicalUrl($request),
            $this->statusCode($request));
    }

    private function canonicalUrl(Request $request): string
    {
        return $request->getSchemeAndHttpHost() . $this->canonicalUri($request);
    }

    private function canonicalUri(Request $request): string
    {
        $uri = $request->getPathInfo();
        if ($uri === '/') {
            return $uri;
        }
        return \subStr($uri, 0, -1);
    }

    private function statusCode(Request $request): int
    {
        if ($request->isMethod('GET')) {
            return 301;
        }
        return 308;
    }
}
