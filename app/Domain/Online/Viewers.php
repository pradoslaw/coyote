<?php
namespace Coyote\Domain\Online;

readonly class Viewers
{
    /**
     * @param ViewerUser[] $users
     */
    public function __construct(
        public array $users,
        public int   $guestsCount,
    )
    {
    }

    public function totalCount(): int
    {
        return \count($this->users) + $this->guestsCount;
    }

    /**
     * @return ViewerUser[]
     */
    public function usersWithGroup(): array
    {
        return $this->usersFiltered(fn(ViewerUser $user) => $user->groupName);
    }

    /**
     * @return ViewerUser[]
     */
    public function usersWithoutGroup(): array
    {
        return $this->usersFiltered(fn(ViewerUser $user) => !$user->groupName);
    }

    private function usersFiltered(callable $predicate): array
    {
        return \array_values(\array_filter($this->users, $predicate));
    }
}
