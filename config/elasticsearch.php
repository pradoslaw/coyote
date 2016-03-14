<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Elasticsearch Client Configuration
    |--------------------------------------------------------------------------
    |
    | This array will be passed to the Elasticsearch client.
    | See configuration options here:
    |
    | http://www.elasticsearch.org/guide/en/elasticsearch/client/php-api/current/_configuration.html
    */
    'hosts'     => ['localhost:9200'],

    /*
    |--------------------------------------------------------------------------
    | Default Index Name
    |--------------------------------------------------------------------------
    |
    | This is the index name that Elasticquent will use for all
    | Elasticquent models.
    */
    'default_index' => 'coyote',

    /*
    | Default log location
    */
    'logPath' => storage_path('logs/elasticsearch.log'),

    /*
    | Default log level
    */
    'logLevel' => \Monolog\Logger::INFO
];
