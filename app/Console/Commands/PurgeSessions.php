<?php

namespace Coyote\Console\Commands;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
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
     * @var UserRepository
     */
    private $user;

    /**
     * @param SessionRepository $session
     * @param UserRepository $user
     */
    public function __construct(SessionRepository $session, UserRepository $user)
    {
        parent::__construct();

        $this->session = $session;
        $this->user = $user;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $result = $this->session->all();
        $lifetime = config('session.lifetime') * 60;

        foreach ($result as $row) {
            if ($row['updated_at'] < time() - $lifetime) {
                $this->signout($row);
            } else {
                $this->extend($row);
            }
        }

        $this->session->gc($lifetime);

        $this->info('Session purged.');
    }

    /**
     * @param array $session
     */
    private function extend(array $session)
    {
        if (empty($session['user_id'])) {
            return;
        }

        /** @var \Coyote\User $user */
        $user = $this->user->find($session['user_id']);

        $user->timestamps = false;
        $user->visited_at = Carbon::now();

        $user->save();
    }

    /**
     * @param array $session
     */
    private function signout(array $session)
    {
        if (empty($session['user_id'])) {
            return;
        }

        /** @var \Coyote\User $user */
        $user = $this->user->find($session['user_id']);

        $this->info('Remove ' . $user->name . '\'s session');

        $user->signout(Carbon::createFromTimestamp($session['updated_at']));
        $user->save();
    }
}
