<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Coyote\Services\Parser\Factories\MicroblogFactory;
use Coyote\Services\Parser\Factories\CommentFactory;
use Coyote\Services\Parser\Factories\SigFactory;
use Coyote\Services\Parser\Factories\PmFactory;
use Coyote\Services\Parser\Factories\PostFactory;
use Coyote\Services\Parser\Factories\JobFactory;

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
            return new MicroblogFactory($app);
        });

        $this->app->bind('Parser\Comment', function ($app) {
            return new CommentFactory($app);
        });

        $this->app->bind('Parser\Sig', function ($app) {
            return new SigFactory($app);
        });

        $this->app->bind('Parser\Pm', function ($app) {
            return new PmFactory($app);
        });

        $this->app->bind('Parser\Post', function ($app) {
            return new PostFactory($app);
        });

        $this->app->bind('Parser\Job', function ($app) {
            return new JobFactory($app);
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
            'Parser\Post',
            'Parser\Job'
        ];
    }
}
