<?php

namespace Coyote\Providers;

use Coyote\Services\GeoIp\Cache;
use Coyote\Services\GeoIp\GeoIp;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Cache\Repository;

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
        $this->app->bind('geo-ip', function ($app) {
            return new Cache(
                new GeoIp(
                    new Client(),
                    $app['config']->get('services.geo-ip.host'),
                    $app['config']->get('services.geo-ip.port')
                ),
                $app[Repository::class]
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
        return ['geo-ip'];
    }
}
