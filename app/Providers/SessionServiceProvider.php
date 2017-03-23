<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Coyote\Services\Session\Viewers;
use Coyote\Services\Session\Handler;
use Illuminate\Contracts\Cache\Repository as CacheContract;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['session']->extend('coyote', function ($app) {
            $lifetime = $app['config']->get('session.lifetime');

            return (new Handler($app[CacheContract::class], $lifetime))->setContainer($app);
        });

        $this->app->bind('session.viewers', function ($app) {
            return new Viewers(
                $app['Coyote\Repositories\Eloquent\SessionRepository'],
                $app['Illuminate\Http\Request']
            );
        });
    }
}
