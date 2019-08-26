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

        if ($this->option('force') || $this->confirm("Do you want to create index $index in Elasticsearch?", true)) {
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
                                            "keep_symbols_filter"
                                        ]
                                    ],
                                    "default_analyzer" => [
                                        "tokenizer" => "whitespace",
                                        "filter" => [
                                            "lowercase",
                                            "keep_symbols_filter"
                                        ]
                                    ]
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
}
