<?php

namespace Coyote\Providers;

use Coyote\Repositories\Contracts\SessionRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Coyote\Services\Session\Viewers;
use Coyote\Services\Session\Handler;

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
            return new Handler($app);
        });

        $this->app->bind('session.viewers', function ($app) {
            return new Viewers(
                $app[SessionRepositoryInterface::class],
                $app[UserRepositoryInterface::class],
                $app[Request::class]
            );
        });
    }
}
