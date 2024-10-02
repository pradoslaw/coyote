<?php
namespace Coyote\Domain\Online;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;
use Illuminate\Support;

readonly class SessionRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function sessionsIn(string $prefixPath): SessionsSnapshot
    {
        [$guests, $users] = $this->guestsAndUsersChunks($prefixPath);
        return new SessionsSnapshot(
            users:$users->pluck('user_id')->toArray(),
            guestsCount:$guests->pluck('count')->first(default:0),
        );
    }

    private function guestsAndUsersChunks(string $prefixPath): Support\Collection
    {
        return $this->connection->table('sessions')
            ->where('robot', '=', '')
            ->whereLike('path', "$prefixPath%")
            ->groupBy('user_id')
            ->get(['user_id', new Expression('COUNT(*) AS count')])
            ->partition('user_id', value:null);
    }
}
