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
    'hosts' => [ env('ELASTICSEARCH_HOST', 'localhost:9200')],

    /*
    |--------------------------------------------------------------------------
    | Default Index Name
    |--------------------------------------------------------------------------
    |
    | This is the index name that Elasticquent will use for all
    | Elasticquent models.
    */
    'default_index' =>  env('ELASTICSEARCH_INDEX', 'coyote'),

    /*
    | Default log locationK
    */
    'logPath' => storage_path('logs/elasticsearch.log'),

    /*
    | Default log level
    */
    'logLevel' => \Monolog\Logger::WARNING,

    'stopwords' => ['a', 'an', 'and', 'are', 'as', 'at', 'be', 'but', 'by', 'for', 'if', 'in', 'into', 'is', 'it',
        'no', 'not', 'of', 'on', 'or', 's', 'such', 't', 'that', 'the', 'their', 'then', 'there', 'these', 'they', 'this',
        'to', 'was', 'will', 'with', 'vol', 'o.o.'
    ]
];
