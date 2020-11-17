<?php

namespace Coyote\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;

class PurgeRecentTopicsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'topics:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh materialized view';

    private Connection $db;

    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->db = $connection;
    }

    public function handle()
    {
        $this->db->statement('REFRESH MATERIALIZED VIEW topic_recent');

        $this->info('Done.');
    }
}
