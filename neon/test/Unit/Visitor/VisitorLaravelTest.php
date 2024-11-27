<?php
namespace Neon\Test\Unit\Visitor;

use Coyote\User;
use Illuminate\Auth\AuthManager;
use Neon\Laravel\LaravelVisitor;
use Neon\Test\BaseFixture;
use PHPUnit\Framework\TestCase;
use Tests\Integration\BaseFixture\Server\Laravel;

class VisitorLaravelTest extends TestCase
{
    use Laravel\Application;
    use BaseFixture\PublicImageUrl;

    /**
     * @test
     */
    public function avatarUrl(): void
    {
        $this->publicImageBaseUrl('http://cdn.com/public');
        $this->loginUser($this->userWithAvatar('foo.png'));
        $visitor = new LaravelVisitor($this->laravel->app);
        $this->assertSame('http://cdn.com/public/foo.png', $visitor->loggedInUserAvatarUrl());
    }

    /**
     * @test
     */
    public function missingAvatar(): void
    {
        $this->loginUser($this->userWithoutAvatar());
        $visitor = new LaravelVisitor($this->laravel->app);
        $this->assertNull($visitor->loggedInUserAvatarUrl());
    }

    /**
     * @test
     */
    public function guest(): void
    {
        $this->loginGuest();
        $visitor = new LaravelVisitor($this->laravel->app);
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
