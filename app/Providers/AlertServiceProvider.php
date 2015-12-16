<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Coyote\Alert\Providers\Post\Login as Alert_Post_Login;

class AlertServiceProvider extends ServiceProvider
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
        $this->app->bind('Alert\Post\Login', function ($app) {
            return new Alert_Post_Login(
                $app['Coyote\Repositories\Eloquent\AlertRepository']
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['Alert\Post\Login'];
    }
}
