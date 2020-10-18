<?php

namespace Coyote\Http;

use Coyote\Http\Middleware\ForceRootUrl;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        Middleware\TrustProxies::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            Middleware\SetupGuestCookie::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            Middleware\DefaultBindings::class,
            Middleware\FirewallBlacklist::class
        ],
        'api' => [
            'throttle:60,1',
            'bindings',
            'bindings.default',
            ForceRootUrl::class
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'          => Middleware\Authenticate::class,
        'auth.basic'    => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings'      => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'throttle'      => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'bindings.default'  => Middleware\DefaultBindings::class,
        'can'           => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'         => Middleware\RedirectIfAuthenticated::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'signed'        => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'verified'      => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'adm'           => Middleware\AdmAccess::class,
        'forum.write'   => Middleware\ForumWrite::class,
        'forum.url'     => Middleware\RedirectIfUrl::class,
        'topic.access'  => Middleware\RedirectIfMoved::class,
        'job.session'   => Middleware\VerifyJobSession::class,
        'job.forget'    => Middleware\ForgetJobDraft::class,
        'job.redirect'  => Middleware\PermanentRedirect::class,
        'topic.scroll'  => Middleware\RedirectToPost::class,
        'post.response' => Middleware\PostSubmitResponse::class,
        'wiki.access'   => Middleware\WikiAccess::class,
        'wiki.lock'     => Middleware\WikiLock::class,
        'wiki.legacy'   => Middleware\WikiLegacy::class,
        'page.hit'      => Middleware\PageHit::class,
        'geocode'       => Middleware\GeocodeIp::class
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
