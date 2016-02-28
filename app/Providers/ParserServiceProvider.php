<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Coyote\Parser\Scenarios\Microblog as Parser_Microblog;
use Coyote\Parser\Scenarios\Comment as Parser_Comment;
use Coyote\Parser\Scenarios\Sig as Parser_Sig;
use Coyote\Parser\Scenarios\Pm as Parser_Pm;
use Coyote\Parser\Scenarios\Post as Parser_Post;

class ParserServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Parser\Microblog', function ($app) {
            return new Parser_Microblog($app);
        });

        $this->app->bind('Parser\Comment', function ($app) {
            return new Parser_Comment($app);
        });

        $this->app->bind('Parser\Sig', function ($app) {
            return new Parser_Sig($app);
        });

        $this->app->bind('Parser\Pm', function ($app) {
            return new Parser_Pm($app);
        });

        $this->app->bind('Parser\Post', function ($app) {
            return new Parser_Post($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        /*
         * UWAGA! Po dodaniu nowego elementu do tablicy trzeba wykonac php artisan clear-compiled
         */
        return [
            'Parser\Microblog',
            'Parser\Comment',
            'Parser\Sig',
            'Parser\Pm',
            'Parser\Post'
        ];
    }
}
