<?php
namespace Coyote\Domain\Online;

use Illuminate\Database\Connection;
use Illuminate\Support;

readonly class Sessions
{
    public function __construct(private Connection $connection)
    {
    }

    public function viewersIn(string $prefixPath): Viewers
    {
        $sessions = $this->fetchUserIdsAndGuestsNull($prefixPath);
        $users = $sessions->filter(fn(?int $userId) => $userId !== null);
        return new Viewers(
            users:$users->unique()->toArray(),
            guestsCount:$sessions->count() - $users->count(),
        );
    }

    private function fetchUserIdsAndGuestsNull(string $prefixPath): Support\Collection
    {
        return $this->connection->table('sessions')
            ->where('robot', '=', '')
            ->whereLike('path', "$prefixPath%")
            ->pluck('user_id');
    }
}
