<?php
namespace Neon\Test\Unit\Navigation\Fixture;

use Neon\Domain\Visitor;

readonly class LoggedInUser implements Visitor
{
    private function __construct(
        private bool    $loggedIn,
        private ?string $avatarUrl,
    )
    {
    }

    public static function guest(): self
    {
        return new LoggedInUser(false, null);
    }

    public static function withAvatar(string $avatarUrl): self
    {
        return new LoggedInUser(true, $avatarUrl);
    }

    public static function withoutAvatar(): self
    {
        return new LoggedInUser(true, null);
    }

    public function loggedIn(): bool
    {
        return $this->loggedIn;
    }

    public function loggedInUserAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }
}
