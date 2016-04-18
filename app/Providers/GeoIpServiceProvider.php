<?php

namespace Coyote\Providers;

use Coyote\Services\GeoIp\GeoIp;
use Guzzle\Http\Client;
use Illuminate\Support\ServiceProvider;

class GeoIpServiceProvider extends ServiceProvider
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
        $this->app->bind('GeoIp', function ($app) {
            return new GeoIp(
                new Client(),
                $app['config']->get('services.geo-ip.host'),
                $app['config']->get('services.geo-ip.port')
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['GeoIp'];
    }
}
