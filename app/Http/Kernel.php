<?php namespace Coyote\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Coyote\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // todo: nieprawidlowe dzialanie middleware, tymczasowo zakomentowane
            //\Coyote\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            Middleware\DefaultBindings::class,
            \Coyote\Http\Middleware\FirewallBlacklist::class
        ],
        'api' => [
            'throttle:60,1',
            'bindings'
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
//        'bindings.default'  => Middleware\DefaultBindings::class,
        'can'           => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'         => Middleware\RedirectIfAuthenticated::class,
        'adm'           => Middleware\AdmAccess::class,
        'forum.access'  => Middleware\ForumAccess::class,
        'forum.write'   => Middleware\ForumWrite::class,
        'forum.url'     => Middleware\RedirectIfUrl::class,
        'topic.access'  => Middleware\RedirectIfMoved::class,
        'job.session'   => Middleware\VerifyJobSession::class,
        'job.revalidate'=> Middleware\RevalidateJobSession::class,
        'job.redirect'  => Middleware\PermanentRedirect::class,
        'topic.scroll'  => Middleware\ScrollToPost::class,
        'post.response' => Middleware\PostSubmitResponse::class,
        'comment.access' => Middleware\CommentAccess::class,
        'wiki.access'   => Middleware\WikiAccess::class,
        'wiki.lock'     => Middleware\WikiLock::class,
        'page.hit'      => Middleware\PageHit::class,
        'cache'         => Middleware\CacheController::class,
        'geocode'       => Middleware\GeocodeIp::class
    ];
}
