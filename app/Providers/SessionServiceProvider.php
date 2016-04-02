<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Coyote\Session\Viewers;
use Coyote\Session\Handler;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->session->extend('coyote', function ($app) {
            $connectionName     = $app['config']->get('session.connection');
            $databaseConnection = $app['db']->connection($connectionName);

            $table = $databaseConnection->getTablePrefix() . $app['config']->get('session.table');

            return new Handler($databaseConnection, $table);
        });

        $this->app->bind('viewers', function ($app) {
            return new Viewers(
                $app['Coyote\Repositories\Eloquent\SessionRepository'],
                $app['Illuminate\Http\Request']
            );
        });
    }
}
