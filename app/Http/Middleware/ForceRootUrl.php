<?php

namespace Coyote\Http\Middleware;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Http\Request;

class ForceRootUrl
{
    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * ForceRootUrl constructor.
     * @param UrlGenerator $urlGenerator
     */
    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $this->urlGenerator->forceRootUrl(config('app.url'));

        return $next($request);
    }
}
