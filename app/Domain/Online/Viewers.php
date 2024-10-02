<?php
namespace Coyote\Domain\Online;

readonly class Viewers
{
    public function __construct(public array $users, public int $guestsCount)
    {
    }

    public function coalesceUser(int $userId): self
    {
        if (\in_array($userId, $this->users)) {
            return $this;
        }
        return $this->mapUsers([...$this->users, $userId]);
    }

    public function coalesceGuest(): self
    {
        if ($this->guestsCount === 0) {
            return new self($this->users, 1);
        }
        return $this;
    }

    private function mapUsers(array $users): self
    {
        return new self($users, $this->guestsCount);
    }
}
