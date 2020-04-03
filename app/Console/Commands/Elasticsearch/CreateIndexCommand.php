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
                                    "split_on_numerics" => false, // If true causes "j2se" to be three tokens; "j" "2" "se".
                                    "preserve_original" => true, // If true includes original words in subwords: "500-42" ⇒ "500-42"
                                    "split_on_case_change" => false, // If true causes "PowerShot" to be two tokens; ("Power-Shot" remains two parts regards).
                                    "catenate_words" => true, // If true causes maximum runs of word parts to be catenated: "wi-fi" ⇒ "wifi". Defaults
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
                                ],
                                // elasticsearch completion suggestion
                                "completion_analyzer" => [
                                    "tokenizer" => "whitespace",
                                    "filter" => [
                                        "lowercase",
                                        "keep_symbols_filter",
                                        "asciifolding"
                                    ]
                                ],
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
                            "text" => [
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
                            ],
                            "host" => [
                                "type" => "text",
                                "analyzer" => "keyword"
                            ],
                            "browser" => [
                                "type" => "text",
                                "analyzer" => "keyword"
                            ],
                            "fingerprint" => [
                                "type" => "text",
                                "analyzer" => "keyword"
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
                            "actor" => [
                                "type" => "object",
                                "properties" => [
                                    "displayName" => [
                                        "type" => "text",
                                        // ability to search case insensitive
                                        "analyzer" => "keyword_analyzer"
                                    ]
                                ]
                            ],
                            "posts" => [
                                "properties" => [
                                    "text" => [
                                        "type" => "text",
                                        "analyzer" => "stopwords_analyzer"
                                    ]
                                ]
                            ],
                            "subject" => [
                                "type" => "text",
                                "analyzer" => "stopwords_analyzer"
                            ],
                            "user_name" => [
                                "type" => "text",
                                // ability to search case insensitive
                                "analyzer" => "keyword_analyzer"
                            ],
                            "suggest" => [
                                "type" => "completion",
                                "analyzer" => "completion_analyzer",
                                "contexts" => [
                                    [
                                        "name" => "model",
                                        "type" => "category",
                                        "path" => "model"
                                    ],
                                    [
                                        "name" => "category",
                                        "type" => "category"
                                    ]
                                ]
//                                "preserve_position_increments" => false
                            ]
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
