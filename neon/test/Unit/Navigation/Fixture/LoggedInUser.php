<?php
namespace Neon\Test\Unit\Navigation\Fixture;

use Neon\Domain\Visitor;

readonly class LoggedInUser implements Visitor
{
    private function __construct(private ?string $avatarUrl)
    {
    }

    public static function guest(): self
    {
        return new LoggedInUser(null);
    }

    public static function withAvatar(string $avatarUrl): self
    {
        return new LoggedInUser($avatarUrl);
    }

    public function loggedIn(): bool
    {
        return $this->avatarUrl !== null;
    }

    public function loggedInUserAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }
}
