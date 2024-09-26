<?php
namespace Coyote\Providers;

use Coyote\Repositories\Redis\SessionRepository;
use Coyote\Services\Session\Handler;
use Coyote\Services\Session\Registered;
use Coyote\Services\Session\Renderer;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app['session']->extend('coyote', function ($app) {
            return new Handler($app[SessionRepository::class], $app);
        });
        $this->app->bind('session.viewers', function ($app) {
            return new Renderer(
                $app[Connection::class],
                $app[Registered::class],
                $app[Request::class],
            );
        });
    }
}
