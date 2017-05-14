<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Coyote\Repositories\Contracts\NotificationRepositoryInterface;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

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

            $class = '\\Coyote\\Services\\Notification\\Providers\\' . implode('\\', array_map('ucwords', $segments));

            $this->app->bind($provider, function ($app) use ($class) {
                return new $class(
                    $app[NotificationRepositoryInterface::class]
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
            'notification.microblog.login',
            'notification.microblog.subscriber',
            'notification.microblog.vote',

            'notification.post.login',
            'notification.post.delete',
            'notification.post.subscriber',
            'notification.post.comment.login',
            'notification.post.vote',
            'notification.post.accept',

            'notification.topic.subscriber',
            'notification.topic.delete',
            'notification.topic.move',
            'notification.topic.subject',

            'notification.wiki.subscriber',
            'notification.wiki.comment',

            'notification.pm'
        ];
    }
}
