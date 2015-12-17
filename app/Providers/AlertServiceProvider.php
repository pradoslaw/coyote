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
        foreach ($this->provides() as $provider) {
            $segments = explode('\\', $provider);
            array_shift($segments);

            $class = '\\Coyote\\Alert\\Providers\\' . implode('\\', $segments);

            $this->app->bind($provider, function ($app) use ($class) {
                return new $class(
                    $app['Coyote\Repositories\Contracts\AlertRepositoryInterface']
                );
            });
        }

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
