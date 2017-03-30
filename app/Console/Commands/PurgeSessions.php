<?php

namespace Coyote\Console\Commands;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\GuestRepositoryInterface as GuestRepository;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Session;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;

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
     * @var GuestRepository
     */
    private $guest;

    /**
     * @param SessionRepository $session
     * @param UserRepository $user
     * @param GuestRepository $guest
     */
    public function __construct(SessionRepository $session, UserRepository $user, GuestRepository $guest)
    {
        parent::__construct();

        $this->session = $session;
        $this->user = $user;
        $this->guest = $guest;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $result = $this->session->all();
        // convert minutes to seconds
        $lifetime = config('session.lifetime') * 60;

        app(Connection::class)->transaction(function () use ($result, $lifetime) {
            foreach ($result as $row) {
                if ($row->expired($lifetime)) {
                    $this->signout($row);
                } else {
                    $this->extend($row);
                }
            }

            $this->session->gc($lifetime);
        });

        $this->info('Session purged.');
    }

    /**
     * @param Session $session
     */
    private function extend($session)
    {
        if (empty($session->userId)) {
            return;
        }

        /** @var \Coyote\User $user */
        $user = $this->user->find($session->userId, ['id', 'visited_at']);

        $user->timestamps = false;
        $user->visited_at = Carbon::createFromTimestamp($session->updatedAt);

        $user->save();
    }

    /**
     * @param Session $session
     */
    private function signout($session)
    {
        $this->guest->save($session);

        if (empty($session->userId)) {
            return;
        }

        /** @var \Coyote\User $user */
        $user = $this->user->find($session->userId);

        $this->info('Remove ' . $user->name . '\'s session');

        $user->visits += 1;
        $user->is_online = false;
        $user->save();
    }
}
