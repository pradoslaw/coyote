<?php

namespace Coyote\Console\Commands\Elasticsearch;

use Illuminate\Console\Command;

class DropIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:drop {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop Elasticsearch index.';


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
     * @return int
     */
    public function handle()
    {
        $index = config('elasticsearch.default_index');

        if ($this->option('force') || $this->confirm("Do you want to drop index $index in Elasticsearch?", true)) {
            $client = app('elasticsearch');

            if ($client->indices()->exists(['index' => $index])) {
                $client->indices()->delete(['index' => $index]);
            }

            $this->info('Done.');
        }

        return 0;
    }
}
