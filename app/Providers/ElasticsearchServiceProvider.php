<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Elasticsearch\ClientBuilder;

class ElasticsearchServiceProvider extends ServiceProvider
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
        $this->app->singleton('Elasticsearch', function () {
            $logger = ClientBuilder::defaultLogger(
                $this->app['config']->get('elasticsearch.logPath'), $this->app['config']->get('elasticsearch.logLevel')
            );

            return ClientBuilder::create()
                ->setHosts($this->app['config']->get('elasticsearch.hosts'))
                ->setLogger($logger)
                ->build();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['Elasticsearch'];
    }
}
