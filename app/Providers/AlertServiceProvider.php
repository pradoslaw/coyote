<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Coyote\Repositories\Contracts\AlertRepositoryInterface;

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
            $segments = explode('.', $provider);
            array_shift($segments);

            $class = '\\Coyote\\Services\\Alert\\Providers\\' . implode('\\', array_map('ucwords', $segments));

            $this->app->bind($provider, function ($app) use ($class) {
                return new $class(
                    $app[AlertRepositoryInterface::class]
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
            'alert.microblog.login',
            'alert.microblog.subscriber',
            'alert.microblog.vote',

            'alert.post.login',
            'alert.post.delete',
            'alert.post.subscriber',
            'alert.post.comment.login',
            'alert.post.vote',
            'alert.post.accept',

            'alert.topic.subscriber',
            'alert.topic.delete',
            'alert.topic.move',
            'alert.topic.subject',
            
            'alert.wiki.subscriber',
            'alert.wiki.comment',

            'alert.pm'
        ];
    }
}
