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
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Coyote\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Coyote\Http\Middleware\VerifyCsrfToken::class,
        \Coyote\Http\Middleware\FirewallBlacklist::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // @todo nie wiem czemu po upgrade laravela, nie dzialaja reguly z grupy web
//            \Coyote\Http\Middleware\EncryptCookies::class,
//            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
//            \Illuminate\Session\Middleware\StartSession::class,
//            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
//            \Coyote\Http\Middleware\VerifyCsrfToken::class,
//            \Coyote\Http\Middleware\FirewallBlacklist::class
        ],
        'api' => [
            'throttle:60,1',
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
        'can'           => \Illuminate\Foundation\Http\Middleware\Authorize::class,
        'guest'         => Middleware\RedirectIfAuthenticated::class,
        'adm'           => Middleware\AdmAccess::class,
        'forum.access'  => Middleware\ForumAccess::class,
        'forum.write'   => Middleware\ForumWrite::class,
        'topic.access'  => Middleware\RedirectIfMoved::class,
        'job.session'   => Middleware\VerifyJobSession::class,
        'job.revalidate'=> Middleware\RevalidateJobSession::class,
        'topic.scroll'  => Middleware\ScrollToPost::class,
        'post.response' => Middleware\PostSubmitResponse::class,
        'comment.access' => Middleware\CommentAccess::class,
        'wiki.access'   => Middleware\WikiAccess::class,
        'wiki.lock'     => Middleware\WikiLock::class,
        'page.hit'      => Middleware\PageHit::class
    ];
}
