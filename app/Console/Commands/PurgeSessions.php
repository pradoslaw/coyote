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
        // dlugosc sesji okreslona jest na 120 min. nie wiem czemu nie moge okreslic krotszej wartosci, gdyz
        // wywalaja sie testy na travisie. niemniej jednak, usuwamy uzytkownikow z tabeli sessions, juz po 15
        // min nieaktywnosci
        session()->getHandler()->gc(15 * 60);

        $this->info('Session purged.');
    }
}
