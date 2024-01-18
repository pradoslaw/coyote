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
        return \str_ends_with($request->getUri(), '/');
    }

    private function redirectToCanonical(Request $request): RedirectResponse
    {
        return redirect(
            $request->getPathInfo(),
            $this->statusCode($request));
    }

    private function statusCode(Request $request): int
    {
        if ($request->isMethod('POST')) {
            return 308;
        }
        return 301;
    }
}
