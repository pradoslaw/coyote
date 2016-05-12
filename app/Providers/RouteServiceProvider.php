<?php

namespace Coyote\Providers;

use Coyote\Repositories\Contracts\ForumRepositoryInterface;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;
use Coyote\Repositories\Contracts\PastebinRepositoryInterface;
use Coyote\Repositories\Contracts\PostRepositoryInterface;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WikiRepositoryInterface;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Coyote\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    public function boot(Router $router)
    {
        $router->pattern('id', '[0-9]+');
        $router->pattern('forum', '[A-Za-z\-\_\/]+');
        $router->pattern('tag', '([a-ząęśżźćółń0-9\-\.\#\+])+');
        $router->pattern('slug', '.*');
        $router->pattern('path', '.*');

        $router->model('user', UserRepositoryInterface::class);
        $router->model('post', PostRepositoryInterface::class);
        $router->model('topic', TopicRepositoryInterface::class);
        $router->model('pastebin', PastebinRepositoryInterface::class);
        $router->model('microblog', MicroblogRepositoryInterface::class);
        $router->model('wiki', WikiRepositoryInterface::class);

        $router->bind('forum', function ($slug) {
            return $this->app->make(ForumRepositoryInterface::class, [$this->app])->where('slug', $slug)->firstOrFail();
        });

        parent::boot($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $this->mapWebRoutes($router);
        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function mapWebRoutes(Router $router)
    {
        $router->group([
            'namespace' => $this->namespace, 'middleware' => 'web',
        ], function ($router) {
            require app_path('Http/Routes/Misc.php');
            require app_path('Http/Routes/Forum.php');
            require app_path('Http/Routes/Job.php');
            require app_path('Http/Routes/Microblog.php');
            require app_path('Http/Routes/User.php');
            require app_path('Http/Routes/Pastebin.php');
            require app_path('Http/Routes/Adm.php');
            require app_path('Http/Routes/Wiki.php'); // must be at the end
        });
    }
}
