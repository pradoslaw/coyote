<?php
namespace Coyote\Domain\Online;

use Coyote\User;
use Illuminate\Database\Eloquent;

/**
 * For development purposes only.
 */
readonly class FakeSessionRepository extends SessionRepository
{
    public function sessionsIn(string $prefixPath): SessionsSnapshot
    {
        if ($prefixPath === '/') {
            return new SessionsSnapshot(
                users:$this->id(User::query()->inRandomOrder(rand(1, 100))),
                guestsCount:\rand(450, 900),
            );
        }
        return new SessionsSnapshot(
            users:[
                ...$this->id(User::query()->whereNotNull('group_name')->limit(1)),
                ...$this->id(User::query()->inRandomOrder()->limit(\rand(5, 7))),
            ],
            guestsCount:\rand(5, 90),
        );
    }

    private function id(Eloquent\Builder $builder): array
    {
        return $builder->pluck('id')->toArray();
    }
}
