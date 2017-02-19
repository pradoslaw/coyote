<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
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
     * @var SessionRepository
     */
    private $session;

    /**
     * @param SessionRepository $session
     */
    public function __construct(SessionRepository $session)
    {
        parent::__construct();

        $this->session = $session;
    }

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

        if (rand(1, 20) <= 2) {
            $this->session->purge();
        }

        $this->info('Session purged.');
    }
}
