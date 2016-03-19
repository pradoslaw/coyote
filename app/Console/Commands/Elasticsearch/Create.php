<?php

namespace Coyote\Console\Commands\Elasticsearch;

use Illuminate\Console\Command;

class Create extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:create';

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

        if ($this->confirm("Do you want to create index $index in Elasticsearch?", true)) {
            $client = app('Elasticsearch');

            $params = [
                'index' => $index,
                'body' => [
                    'settings' => [
                        "index" => [
                            "analysis" => [
                                "analyzer" => [
                                    "analyzer_keyword" => [
                                        "tokenizer" => "keyword",
                                        "filter" => "lowercase"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $client->indices()->close(['index' => $index]);
            $client->indices()->putSettings($params);
            $client->indices()->open(['index' => $index]);

            $this->info('Done.');
        }
    }
}
