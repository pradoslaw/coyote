<?php

namespace Boduch\Grid;

use Boduch\Grid\Console\GridMakeCommand;
use Boduch\Grid\GridBuilder;
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
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'laravel-grid');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(GridMakeCommand::class);

        $this->app->singleton('grid.builder', function ($app) {
            return new GridBuilder($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['grid.builder', GridBuilder::class];
    }
}
