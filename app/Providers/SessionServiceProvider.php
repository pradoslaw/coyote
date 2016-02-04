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
            $connectionName     = $this->app->config->get('session.connection');
            $databaseConnection = $app->app->db->connection($connectionName);

            $table = $databaseConnection->getTablePrefix() . $app['config']['session.table'];

            return new Handler($databaseConnection, $table);
        });

        $this->app->bind('Session\Viewers', function ($app) {
            return new Viewers(
                $app['Coyote\Repositories\Eloquent\SessionRepository'],
                $app['Illuminate\Http\Request']
            );
        });
    }
}
