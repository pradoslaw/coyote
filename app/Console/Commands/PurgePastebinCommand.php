<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\PastebinRepositoryInterface as PastebinRepository;
use Illuminate\Console\Command;

class PurgePastebinCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pastebin:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge old pastebin entries.';

    /**
     * @var PastebinRepository
     */
    protected $pastebin;

    /**
     * Create a new command instance.
     *
     * @param PastebinRepository $pastebin
     */
    public function __construct(PastebinRepository $pastebin)
    {
        parent::__construct();

        $this->pastebin = $pastebin;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->pastebin->purge();
        $this->info('Done.');

        return 0;
    }
}
