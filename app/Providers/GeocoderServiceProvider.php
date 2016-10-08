<?php

namespace Coyote\Providers;

use Coyote\Services\Geocoder\Geocoder;
use Coyote\Services\Geocoder\GeocoderInterface;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;

class GeocoderServiceProvider extends ServiceProvider
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
        $this->app->bind(GeocoderInterface::class, function ($app) {
            return new Geocoder(
                new Client(),
                $app['config']->get('services.google-maps.key')
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
        return [GeocoderInterface::class];
    }
}
