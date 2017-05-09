<?php

namespace Coyote\Console\Commands;

use Coyote\Firm;
use Illuminate\Console\Command;

class SetupFirmSlugCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coyote:slug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup firm slug';

    /**
     * Create a new command instance.
     *
     * @return void
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
        $result = Firm::all();

        foreach ($result as $row) {
            $row->name = $row->name;
            $row->save();
        }

        $this->info('Done.');
    }
}
