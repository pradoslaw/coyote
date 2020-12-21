<?php

namespace Coyote\Console\Commands;

use Carbon\Carbon;
use Coyote\Guest;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Services\Elasticsearch\Crawler;
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
     * @param Db $db
     * @param SessionRepository $session
     * @param UserRepository $user
     */
    public function __construct(Db $db, SessionRepository $session, UserRepository $user)
    {
        parent::__construct();

        $this->db = $db;
        $this->session = $session;
        $this->user = $user;
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
            try {
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
            } catch (\Exception $e) {
                logger()->error($e);
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

        return 0;
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

        if (empty($user)) {
            return;
        }

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
    private function signout(Session $session)
    {
        if (!empty($session->guestId)) {
            /** @var Guest $guest */
            $guest = Guest::findOrNew($session->guestId);
            $guest->saveWithSession($session);
        }

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

        // reindex user data in elasticsearch so we can sort users by last activity date
        dispatch(function () use ($user) {
            (new Crawler())->index($user);
        });
    }
}
