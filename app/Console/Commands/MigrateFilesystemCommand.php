<?php

namespace Coyote\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateFilesystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:filesystem';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate filesystem (temp command)';

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
        $this->migratePublic();
        $this->migrateLocal();
    }

    private function migratePublic()
    {
        $files = Storage::disk('public_fs')->files('', true);
        $bar = $this->output->createProgressBar(count($files));

        $bar->start();

        foreach ($files as $file) {
            Storage::disk('public')->put($file, Storage::disk('public_fs')->get($file));

            $bar->advance();
        }

        $bar->finish();
    }

    private function migrateLocal()
    {
        $files = Storage::disk('local_fs')->files('', true);
        $bar = $this->output->createProgressBar(count($files));

        $bar->start();

        foreach ($files as $file) {
            Storage::disk('local')->put($file, Storage::disk('local_fs')->get($file));

            $bar->advance();
        }

        $bar->finish();
    }
}
