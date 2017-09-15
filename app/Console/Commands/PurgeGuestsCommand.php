<?php

namespace Coyote\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection as Db;

class PurgeGuestsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guest:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge old anonymous users data';

    /**
     * @var Db
     */
    protected $db;

    /**
     * @param Db $db
     */
    public function __construct(Db $db)
    {
        parent::__construct();

        $this->db = $db;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->db->delete("DELETE FROM guests WHERE user_id IS NULL AND updated_at < NOW()  - INTERVAL '1 month'");
        $this->db->delete("DELETE FROM topic_track WHERE user_id IS NULL AND marked_at < NOW() - INTERVAL '1 month'");
        $this->db->delete("DELETE FROM forum_track WHERE user_id IS NULL AND marked_at < NOW() - INTERVAL '1 month'");

        $this->info('Done.');
    }
}
