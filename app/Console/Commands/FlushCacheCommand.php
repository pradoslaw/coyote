<?php

namespace Coyote\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as Cache;

class FlushCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:flush {--tag=} {--key=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush cache by specific tag or key';

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        parent::__construct();

        $this->cache = $cache;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->cache->tags($this->option('tag'))->flush();

        $this->info('Done.');

        return 0;
    }
}
