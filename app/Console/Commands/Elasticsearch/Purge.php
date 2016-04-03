<?php

namespace Coyote\Console\Commands\Elasticsearch;

use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Console\Command;

class Purge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge expired job offers from Elasticsearch index.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $es = app('Elasticsearch');
        $es->deleteByQuery([
            'index' => config('elasticsearch.default_index'),
            'type'  => 'jobs',
            'body'  => [
                'query' => [
                    'filtered' => [
                        'filter' => [
                            ['range' => ['deadline_at' => ['lt' => 'now']]]
                        ]
                    ]
                ]
            ]
        ]);
    }
}
