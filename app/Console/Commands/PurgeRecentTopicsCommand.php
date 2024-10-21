<?php
namespace Coyote\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;

class PurgeRecentTopicsCommand extends Command
{
    protected $signature = 'topics:purge';
    protected $description = 'Refresh materialized view';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    public function handle(): int
    {
        $this->connection->statement('REFRESH MATERIALIZED VIEW topic_recent;');
        $this->info('Done.');
        return 0;
    }
}
