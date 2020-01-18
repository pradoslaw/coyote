<?php

namespace Coyote\Providers;

use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;
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
        $this->app->singleton('elasticsearch', function ($app) {
//            $logger = ClientBuilder::defaultLogger(
//                $app['config']->get('elasticsearch.logPath'),
//                $app['config']->get('elasticsearch.logLevel')
//            );

            return ClientBuilder::create()
                ->setHosts($app['config']->get('elasticsearch.hosts'))
//                ->setLogger($logger)
                ->build();
        });

        $this->app->bind(QueryBuilderInterface::class, QueryBuilder::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['elasticsearch', QueryBuilderInterface::class];
    }
}
