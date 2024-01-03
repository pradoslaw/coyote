<?php
namespace Coyote\Http\Middleware\Forum;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation;

class WikiLegacy extends AbstractMiddleware
{
    public function handle(Request $request, Closure $next): HttpFoundation\Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($response->original === null || is_string($response->original)) {
            return $response;
        }

        $url = $this->redirectedUrl($response->original->getData()['wiki']->text);
        if ($url === null) {
            return $response;
        }
        return redirect()->to($url);
    }

    private function redirectedUrl(string $content): ?string
    {
        $plain = strip_tags($content);
        if (str_starts_with($plain, '#REDIRECT')) {
            return trim(substr($plain, 9));
        }
        return null;
    }
}
