<?php
namespace Neon\Test\Unit\Laravel;

use Coyote\User;
use Illuminate\Auth\AuthManager;
use Neon\Laravel\AppVisitor;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Server\Laravel\Application;

class AppVisitorTest extends TestCase
{
    use Application;

    /**
     * @test
     */
    public function avatarUrl(): void
    {
        $this->loginUser($this->userWithAvatar('foo.png'));
        $visitor = new AppVisitor($this->laravel->app);
        $this->assertSame('foo.png', $visitor->loggedInUserAvatarUrl());
    }

    /**
     * @test
     */
    public function missingAvatar(): void
    {
        $this->loginUser($this->userWithoutAvatar());
        $visitor = new AppVisitor($this->laravel->app);
        $this->assertNull($visitor->loggedInUserAvatarUrl());
    }

    /**
     * @test
     */
    public function guest(): void
    {
        $this->loginGuest();
        $visitor = new AppVisitor($this->laravel->app);
        $this->assertNull($visitor->loggedInUserAvatarUrl());
    }

    private function loginUser(User $user): void
    {
        /** @var AuthManager $auth */
        $auth = $this->laravel->app->get(AuthManager::class);
        $auth->login($user);
    }

    private function loginGuest(): void
    {
        /** @var AuthManager $auth */
        $auth = $this->laravel->app->get(AuthManager::class);
        $auth->logout();
    }

    private function userWithoutAvatar(): User
    {
        $user = new User();
        $user->name = \uniqid();
        $user->email = '';
        return $user;
    }

    private function userWithAvatar(string $filename): User
    {
        $user = new User();
        $user->name = \uniqid();
        $user->email = '';
        $user->photo->setFilename($filename);
        return $user;
    }
}
