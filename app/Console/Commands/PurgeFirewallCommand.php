<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\FirewallRepositoryInterface as FirewallRepository;
use Coyote\Services\Firewall\Rules;
use Illuminate\Console\Command;
use Illuminate\Cache\Repository as Cache;

class PurgeFirewallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firewall:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge expired firewall entries.';

    /**
     * @var FirewallRepository
     */
    protected $firewall;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * Create a new command instance.
     *
     * @param FirewallRepository $firewall
     * @param Cache $cache
     */
    public function __construct(FirewallRepository $firewall, Cache $cache)
    {
        parent::__construct();

        $this->firewall = $firewall;
        $this->cache = $cache;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->firewall->purge();
        $this->cache->forget(Rules::CACHE_KEY);

        $this->info('Done.');

        return 0;
    }
}
