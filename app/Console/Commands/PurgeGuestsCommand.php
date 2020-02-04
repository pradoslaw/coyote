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
        $this->db->delete("DELETE FROM topic_track USING guests WHERE guests.id = topic_track.guest_id AND topic_track.marked_at < NOW() - INTERVAL '2 weeks' AND guests.user_id IS NULL");
        $this->db->delete("DELETE FROM forum_track USING guests WHERE guests.id = forum_track.guest_id AND forum_track.marked_at < NOW() - INTERVAL '2 weeks' AND guests.user_id IS NULL");
        $this->db->delete("DELETE FROM guests WHERE user_id IS NULL AND updated_at < NOW()  - INTERVAL '2 weeks'");
        $this->db->delete("DELETE FROM activities WHERE created_at < NOW()  - INTERVAL '2 weeks'");

        $this->info('Done.');
    }
}
