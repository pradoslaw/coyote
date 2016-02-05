<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;

class ReputationServiceProvider extends ServiceProvider
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

            $class = '\\Coyote\\Reputation\\' . implode('\\', $segments);

            $this->app->bind($provider, function ($app) use ($class) {
                return new $class(
                    $app['Coyote\Repositories\Contracts\ReputationRepositoryInterface']
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
        /*
         * UWAGA! Po dodaniu nowego elementu do tablicy trzeba wykonac php artisan clear-compiled
         */
        return [
            'Reputation\Post\Vote',
            'Reputation\Post\Accept',

            'Reputation\Microblog\Create',
            'Reputation\Microblog\Vote'
        ];
    }
}
