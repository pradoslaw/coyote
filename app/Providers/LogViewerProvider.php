<?php

namespace Coyote\Providers;

use Coyote\Services\LogViewer\LogViewer;
use Illuminate\Support\ServiceProvider;

class LogViewerProvider extends ServiceProvider
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
        $this->app->bind('log-viewer', function ($app) {
            return new LogViewer($app['filesystem']->disk('log'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['log-viewer'];
    }
}
