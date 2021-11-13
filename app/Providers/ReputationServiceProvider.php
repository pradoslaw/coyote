<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface;

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
            $segments = explode('.', $provider);
            array_shift($segments);

            $class = '\\Coyote\\Services\\Reputation\\' . implode('\\', array_map('ucwords', $segments));

            $this->app->bind($provider, function ($app) use ($class) {
                return new $class(
                    $app[ReputationRepositoryInterface::class]
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
            'reputation.post.vote',
            'reputation.post.accept',

            'reputation.microblog.create',
            'reputation.microblog.vote',

            'reputation.wiki.create',
            'reputation.wiki.update',

            'reputation.guide.create'
        ];
    }
}
