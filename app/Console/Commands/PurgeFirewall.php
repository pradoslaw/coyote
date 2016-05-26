<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\FirewallRepositoryInterface as FirewallRepository;
use Illuminate\Console\Command;

class PurgeFirewall extends Command
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
     * Create a new command instance.
     *
     * @param FirewallRepository $firewall
     */
    public function __construct(FirewallRepository $firewall)
    {
        parent::__construct();

        $this->firewall = $firewall;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->firewall->purge();
        $this->info('Done.');
    }
}
