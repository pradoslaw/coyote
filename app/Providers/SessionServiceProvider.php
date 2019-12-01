<?php

namespace Coyote\Providers;

use Coyote\Repositories\Contracts\SessionRepositoryInterface;
use Coyote\Services\Session\Registered;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Coyote\Services\Session\Renderer;
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
            return new Handler($app[SessionRepositoryInterface::class], $app);
        });

        $this->app->bind('session.viewers', function ($app) {
            return new Renderer(
                $app[Connection::class],
                $app[Registered::class],
                $app[Request::class]
            );
        });
    }
}
