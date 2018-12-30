<?php

namespace Coyote\Console\Commands;

use Illuminate\Console\Command;

class MigrateStreamsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:streams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
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
        $db = app('db');

        $bar = $this->output->createProgressBar($db->connection('mongodb')->collection('streams')->count());
        $bar->start();

        $db->connection('mongodb')->collection('streams')->orderBy('_id')->chunk(10000, function ($results) use ($db, $bar) {
            foreach ($results as $row) {
                unset($row['_id']);

                if ($row['created_at'] instanceof \MongoDB\BSON\UTCDateTime) {
                    $row['created_at'] = $row['created_at']->toDateTime();
                }
                $row = $this->toJson($row);

                $db->table('streams')->insert((array) $row);
                $bar->advance();
            }
        });

        $bar->finish();
    }

    private function toJson($data)
    {
        foreach (['actor', 'object', 'target'] as $key) {
            $data[$key] = json_encode(!empty($data[$key]) ? $data[$key] : []);
        }

        return $data;
    }
}
