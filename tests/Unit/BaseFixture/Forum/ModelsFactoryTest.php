<?php
namespace Tests\Unit\BaseFixture\Forum;

use Coyote\User;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\Concerns;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class ModelsFactoryTest extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use Concerns\InteractsWithDatabase;

    private ModelsFactory $models;
    private Application $app;

    #[Before]
    public function initializeModels(): void
    {
        $this->models = new ModelsFactory();
        $this->app = $this->laravel->app;
    }

    #[Test]
    public function newUser(): void
    {
        $users = $this->usersCount();
        $this->models->newUser();
        $this->assertSame($users + 1, $this->usersCount(),
            'Failed to assert that a new user was created.');
    }

    #[Test]
    public function newUserMany(): void
    {
        $users = $this->usersCount();
        $this->models->newUser();
        $this->models->newUser();
        $this->assertSame($users + 2, $this->usersCount(),
            'Failed to assert that two new users were created.');
    }

    #[Test]
    public function newMicroblogContent(): void
    {
        $this->models->newMicroblog('content');
        $this->assertDatabaseHas('microblogs', ['text' => 'content']);
    }

    #[Test]
    public function newMicroblogReturnId(): void
    {
        $id = $this->models->newMicroblogReturnId();
        $this->assertDatabaseHas('microblogs', ['id' => $id]);
    }

    private function usersCount(): int
    {
        return User::query()->count();
    }
}
