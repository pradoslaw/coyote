<?php

namespace Coyote\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/User/Settings/Ajax',
        '/Microblog/Comment/Show/*',
        '/Forum/Comment/*',
    ];

    public function handle($request, \Closure $next)
    {
        $exceptDomains = [
            'api.' . ltrim(config('session.domain'))
        ];
        foreach ($exceptDomains as $domain) {
            if ($domain === $request->getHost()) {
                return $next($request);
            }
        }
        return parent::handle($request, $next);
    }
}
