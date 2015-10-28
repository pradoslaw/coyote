<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;

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

            return new \Coyote\Session($databaseConnection, $table);
        });
    }
}
