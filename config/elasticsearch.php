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
        'to', 'was', 'will', 'with', 'vol', 'o.o.', 'mgr', 'godz', 'zł', 'www', 'pl', 'ul', 'tel', 'hab', 'prof', 'inż',
        'dr', 'i', 'u', 'aby', 'albo', 'ale', 'ani', 'aż', 'bardzo', 'bez', 'bo', 'bowiem', 'by', 'byli', 'bym', 'był',
        'była', 'było', 'były', 'być', 'będzie', 'będą', 'choć', 'co', 'coraz', 'coś', 'czy', 'czyli', 'często',
        'dla', 'do', 'gdy', 'gdyby', 'gdyż', 'gdzie', 'go', 'ich', 'im', 'inne', 'iż', 'ja', 'jak', 'jakie', 'jako',
        'je', 'jednak', 'jednym', 'jedynie', 'jego', 'jej', 'jest', 'jeszcze', 'jeśli', 'jeżeli', 'już', 'ją', 'kiedy',
        'kilku', 'kto', 'która', 'które', 'którego', 'której', 'który', 'których', 'którym', 'którzy', 'lat', 'lecz',
        'lub', 'ma', 'mają', 'mamy', 'mi', 'miał', 'mimo', 'mnie', 'mogą', 'może', 'można', 'mu', 'musi', 'na', 'nad',
        'nam', 'nas', 'nawet', 'nic', 'nich', 'nie', 'niej', 'nim', 'niż', 'no', 'nowe', 'np', 'nr', 'o', 'od', 'ok',
        'on', 'one', 'oraz', 'pan', 'po', 'pod', 'ponad', 'ponieważ', 'poza', 'przed', 'przede', 'przez', 'przy',
        'raz', 'razie', 'roku', 'również', 'się', 'sobie', 'sposób', 'swoje', 'są', 'ta', 'tak', 'takich', 'takie',
        'także', 'tam', 'te', 'tego', 'tej', 'temu', 'ten', 'teraz', 'też', 'to', 'trzeba', 'tu', 'tych', 'tylko',
        'tym', 'tys', 'tzw', 'tę', 'w', 'we', 'wie', 'więc', 'wszystko', 'wśród', 'właśnie', 'z', 'za', 'zaś',
        'ze', 'że', 'żeby', 'ii', 'iii', 'iv', 'vi', 'vii', 'viii', 'ix', 'xi', 'xii', 'xiii', 'xiv', 'xv'
    ]
];
