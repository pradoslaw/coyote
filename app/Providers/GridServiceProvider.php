<?php

namespace Coyote\Providers;

use Coyote\Services\Grid\Grid;
use Illuminate\Support\ServiceProvider;

class GridServiceProvider extends ServiceProvider
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
        $this->app->bind('grid', function ($app) {
            return new Grid($app['request'], $app['validator']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['grid'];
    }
}
