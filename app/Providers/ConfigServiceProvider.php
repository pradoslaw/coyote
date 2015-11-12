<?php namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Overwrite any vendor / package configuration.
     *
     * This service provider is intended to provide a convenient location for you
     * to overwrite any "vendor" or package configuration that you may want to
     * modify before the application handles the incoming request / command.
     *
     * @return void
     */
    public function register()
    {
        setlocale(LC_ALL, array('pl_PL.UTF-8', 'polish_pol'));
        Carbon::setLocale(config('app.locale'));

        config([
            //
        ]);
    }
}
