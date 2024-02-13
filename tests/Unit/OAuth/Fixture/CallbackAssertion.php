<?php
namespace Tests\Unit\OAuth\Fixture;

use Coyote\User;
use PHPUnit\Framework\Assert;
use Tests\Unit\OAuth;

trait CallbackAssertion
{
    use OAuth\Fixture\Models;

    function assertUserExists(string $email): void
    {
        Assert::assertNotNull($this->userByEmail($email));
    }

    function assertUserHasName(string $email, string $username): void
    {
        Assert::assertSame($username, $this->userByEmail($email)->name);
    }

    function userByEmail(string $email): ?User
    {
        /** @var User $user */
        $user = User::query()->where(['email' => $email])->first();
        return $user;
    }

    function assertUserProvider(int $userId, string $provider): void
    {
        Assert::assertSame($provider, $this->user($userId)->provider);
    }

    function assertUserProviderId(int $userId, string $providerId): void
    {
        Assert::assertSame($providerId, $this->user($userId)->provider_id);
    }

    function user(int $userId): User
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);
        return $user;
    }
}
