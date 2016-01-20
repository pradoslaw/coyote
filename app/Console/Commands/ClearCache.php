<?php

namespace Coyote\Console\Commands;

use Illuminate\Console\Command;
use Cache;

class ClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coyote:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush the Coyote cache';

    /**
     * Create a new command instance.
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
        Cache::flush();
        $this->info('Done.');
    }
}
