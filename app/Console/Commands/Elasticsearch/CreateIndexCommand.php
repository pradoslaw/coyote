<?php

namespace Coyote\Console\Commands\Elasticsearch;

use Illuminate\Console\Command;

class CreateIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:create {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create index with settings in Elasticsearch.';


    /**
     * Mapping constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $index = config('elasticsearch.default_index');

        if (!$this->option('force') && !$this->confirm("Do you want to create index $index in Elasticsearch?", true)) {
            return;
        }

        $client = app('elasticsearch');

        $params = [
            'index' => $index,
            'body' => [
                'settings' => [
                    "index" => [
                        "analysis" => [
                            "filter" => [
                                "common_words_filter" => [
                                    "type" => "stop",
                                    "stopwords" => config('elasticsearch.stopwords')
                                ],
                                "keep_symbols_filter" => [
                                    "type" => "word_delimiter",
                                    "type_table" => [
                                        "# => ALPHANUM",
                                        "+ => ALPHANUM",
                                        "_ => ALPHANUM"
                                    ]
                                ]
                            ],
                            "analyzer" => [
                                // just like keyword type except lowercase filter
                                "keyword_analyzer" => [
                                    "tokenizer" => "keyword",
                                    "filter" => "lowercase"
                                ],
                                // used to index city names
                                "keyword_asciifolding_analyzer" => [
                                    "tokenizer" => "keyword",
                                    "filter" => [
                                        "lowercase",
                                        "asciifolding"
                                    ]
                                ],
                                "stopwords_analyzer" => [
                                    "tokenizer" => "whitespace",
                                    "filter" => [
                                        "lowercase",
                                        "common_words_filter",
                                        "keep_symbols_filter",
                                        "asciifolding"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    '_doc' => [
                        'properties' => [
                            "model" => [
                                "type" => "keyword"
                            ],
                            "created_at" => [
                                "type" => "date",
                                "format" => "yyyy-MM-dd HH:mm:ss"
                            ],
                            "updated_at" => [
                                "type" => "date",
                                "format" => "yyyy-MM-dd HH:mm:ss"
                            ],
                            "deadline_at" => [
                                "type" => "date",
                                "format" => "yyyy-MM-dd HH:mm:ss"
                            ],
                            "boost_at" => [
                                "type" => "date",
                                "format" => "yyyy-MM-dd HH:mm:ss"
                            ],
                            "html" => [
                                "type" => "text",
                                "analyzer" => "stopwords_analyzer"
                            ],
                            "description" => [
                                "type" => "text",
                                "analyzer" => "stopwords_analyzer"
                            ],
                            "requirements" => [
                                "type" => "text",
                                "analyzer" => "stopwords_analyzer"
                            ],
                            "title" => [
                                "type" => "text",
                                "analyzer" => "stopwords_analyzer"
                            ],
                            "is_remote" => [
                                "type" => "boolean"
                            ],
                            "ip" => [
                                "type" => "ip",
//                                    "index" => "not_analyzed"
                            ],
                            "host" => [
                                "type" => "text",
//                                    "index" => "not_analyzed"
                            ],
                            "tags" => [
                                "type" => "text",
                                "fields" => [
                                    "original" => ["type" => "keyword"]
                                ]
                            ],
                            "salary" => [
                                "type" => "float"
                            ],
                            "is_boost" => [
                                "type" => "boolean"
                            ],
                            "is_publish" => [
                                "type" => "boolean"
                            ],
                            "is_ads" => [
                                "type" => "boolean"
                            ],
                            "is_on_top" => [
                                "type" => "boolean"
                            ],
                            "is_highlight" => [
                                "type" => "boolean"
                            ],
                            "locations" => [
                                "type" => "nested",
                                "properties" => [
                                    "label" => [
                                        "type" => "text",
                                        "analyzer" => "stopwords_analyzer"
                                    ],
                                    "city" => [
                                        "type" => "text",
                                        "analyzer" => "keyword_asciifolding_analyzer",
                                        "fields" => [
                                            // aggregate city by this field.
                                            "original" => ["type" => "text", "analyzer" => "keyword_analyzer", "fielddata" => true]
                                        ]
                                    ],
                                    "coordinates" => [
                                        "type" => "geo_point"
                                    ]
                                ]
                            ],
                            "firm" => [
                                "type" => "object",
                                "properties" => [
                                    "name" => [
                                        "type" => "text",
                                        "analyzer" => "stopwords_analyzer",
                                        "fields" => [
                                            // filtrujemy firmy po tym polu
                                            "original" => ["type" => "text", "analyzer" => "keyword_analyzer", "fielddata" => true]
                                        ]
                                    ],
                                    "slug" => [
                                        "type" => "text",
                                        "analyzer" => "keyword_analyzer"
                                    ]
                                ]
                            ],
                        ]
                    ]
                ]
            ]
        ];

        if ($client->indices()->exists(['index' => $index])) {
            $client->indices()->close(['index' => $index]);
            $client->indices()->putSettings($params);
            $client->indices()->open(['index' => $index]);
        } else {
            $client->indices()->create($params);
        }

        $this->info('Done.');
    }
}
