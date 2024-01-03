<?php

namespace Coyote\Http\Middleware\Forum;

use Closure;
use Illuminate\Http\Request;

class WikiLegacy extends AbstractMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        if ($response->original === null || is_string($response->original)) {
            return $response;
        }

        $url = $this->getRedirectedUrl($response->original->getData()['wiki']->text);

        if ($url !== null) {
            return redirect()->to($url);
        }

        return $response;
    }

    /**
     * @param string $content
     * @return string|null
     */
    private function getRedirectedUrl(string $content): ?string
    {
        $plain = strip_tags($content);

        if (substr($plain, 0, 9) !== '#REDIRECT') {
            return null;
        }

        return trim(substr($plain, 9));
    }
}
