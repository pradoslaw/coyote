<?php

namespace Coyote\Console\Commands;

use Illuminate\Console\Command;

class PurgeSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge old sessions.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        session()->getHandler()->gc(config('session.lifetime'));

        $this->info('Session purged.');
    }
}
