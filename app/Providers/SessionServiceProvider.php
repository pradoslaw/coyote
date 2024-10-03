<?php
namespace Coyote\Providers;

use Coyote\Repositories\Redis\SessionRepository;
use Coyote\Services\Session\Handler;
use Illuminate\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app['session']->extend('coyote', function ($app) {
            return new Handler($app[SessionRepository::class], $app);
        });
    }
}
