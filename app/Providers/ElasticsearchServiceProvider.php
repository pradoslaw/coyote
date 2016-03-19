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
        $this->app->singleton('Elasticsearch', function ($app) {
            $logger = ClientBuilder::defaultLogger(
                $app['config']->get('elasticsearch.logPath'), $app['config']->get('elasticsearch.logLevel')
            );

            return ClientBuilder::create()
                ->setHosts($app['config']->get('elasticsearch.hosts'))
                ->setLogger($logger)
                ->build();
        });

        $this->app->bind('Coyote\Elasticsearch\QueryBuilderInterface', 'Coyote\Elasticsearch\QueryBuilder');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['Elasticsearch', 'Coyote\Elasticsearch\QueryBuilderInterface'];
    }
}
