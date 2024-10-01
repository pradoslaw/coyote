<?php
namespace Coyote\Console\Commands;

use Carbon\Carbon;
use Coyote\Guest;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Repositories\Redis\SessionRepository;
use Coyote\Services\Elasticsearch\Crawler;
use Coyote\Session;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;

ini_set('memory_limit', '3G');

class PurgeSessionsCommand extends Command
{
    /** @var string */
    protected $signature = 'session:purge';
    /** @var string */
    protected $description = 'Purge old sessions.';

    public function __construct(
        readonly private Connection        $db,
        readonly private SessionRepository $session,
        readonly private UserRepository    $user)
    {
        parent::__construct();
    }

    public function handle(): int
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
                        ['path' => mb_strtolower($path)],
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

    private function extend(Session $session): void
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

    private function signout(Session $session): void
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

        // user was removed. that's it.
        if ($user->deleted_at !== null) {
            return;
        }

        // reindex user data in elasticsearch so we can sort users by last activity date
        dispatch_sync(function () use ($user) {
            (new Crawler())->index($user);
        });
    }
}
