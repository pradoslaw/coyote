<?php

namespace Coyote\Console\Commands;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\GuestRepositoryInterface as GuestRepository;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Session;
use Illuminate\Console\Command;
use Illuminate\Database\Connection as Db;

class PurgeSessionsCommand extends Command
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
     * @var Db
     */
    private $db;

    /**
     * @var UserRepository
     */
    private $user;

    /**
     * @var GuestRepository
     */
    private $guest;

    /**
     * @param Db $db
     * @param SessionRepository $session
     * @param UserRepository $user
     * @param GuestRepository $guest
     */
    public function __construct(Db $db, SessionRepository $session, UserRepository $user, GuestRepository $guest)
    {
        parent::__construct();

        $this->db = $db;
        $this->session = $session;
        $this->user = $user;
        $this->guest = $guest;
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        $result = $this->session->all();
        // convert minutes to seconds
        $lifetime = config('session.lifetime') * 60;

        $this->user->pushCriteria(new WithTrashed());

        $values = [];

        foreach ($result as $session) {
            if ($session->expired($lifetime)) {
                $this->signout($session);
            } else {
                $this->extend($session);

                $path = str_limit($session->path, 999, '');

                $values[] = array_merge(
                    array_only($session->toArray(), ['id', 'user_id', 'robot']),
                    ['path' => mb_strtolower($path)]
                );
            }
        }

        $this->db->transaction(function () use ($lifetime, $values) {
            $this->db->unprepared('DELETE FROM sessions');

            // make a copy of sessions in postgres for faster calculations (number of visitors for give page etc.)
            $this->db->table('sessions')->insert($values);
            $this->session->gc($lifetime);
        });

        $this->user->resetCriteria();
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
        $user = $this->user->find($session->userId);

        $user->timestamps = false;
        // update only this field:
        $user->visited_at = Carbon::createFromTimestamp($session->updatedAt);
        $user->ip = $session->ip;
        $user->browser = $session->browser;
        $user->is_online = true;

        $user->save();
    }

    /**
     * @param Session $session
     */
    private function signout($session)
    {
        // save user's last visit
        $this->guest->save($session);

        if (empty($session->userId)) {
            return;
        }

        /** @var \Coyote\User $user */
        $user = $this->user->find($session->userId);

        $this->info('Remove ' . $user->name . '\'s session. IP: ' . $session->ip);

        $user->timestamps = false;
        $user->visits += 1;
        $user->is_online = false;

        $user->save();
    }
}
