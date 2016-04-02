<?php namespace Coyote\Providers;

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
        $router->model('user', 'Coyote\User');
        $router->model('post', 'Coyote\Post');
        $router->model('topic', 'Coyote\Topic');

        $router->pattern('id', '[0-9]+');
        $router->pattern('forum', '[A-Za-z\-\_\/]+');
        $router->pattern('tag', '([a-ząęśżźćółń0-9\-\.\#\+])+');
        $router->bind('forum', function ($path) {
            $result = \Coyote\Forum::where('path', $path)->first();

            if (!$result) {
                abort(404);
            }

            return $result;
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
