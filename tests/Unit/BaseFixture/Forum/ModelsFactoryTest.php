<?php
namespace Tests\Unit\BaseFixture\Forum;

use Coyote\Flag;
use Coyote\Post;
use Coyote\User;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\Concerns;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
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

    #[Before]
    public function removeFlags(): void
    {
        Flag::query()->delete();
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
    public function newUserReturnId(): void
    {
        $id = $this->models->newUserReturnId();
        $this->assertDatabaseHas('users', ['id' => $id]);
    }

    #[Test]
    public function newUserName(): void
    {
        $this->models->newUser('Mark');
        $this->assertDatabaseHas('users', ['name' => 'Mark']);
    }

    #[Test]
    public function newUserDeleted(): void
    {
        $this->models->newUserDeleted('Mark');
        $this->assertSoftDeleted('users', ['name' => 'Mark']);
    }

    #[Test]
    public function newUserConfirmedEmail(): void
    {
        $this->models->newUserConfirmedEmail('mail');
        $this->assertDatabaseHas('users', ['email' => 'mail', 'is_confirm' => true]);
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

    #[Test]
    public function newPostReport(): void
    {
        $this->models->newPostReported(reportContent:'report');
        $this->assertDatabaseHas('flags', ['text' => 'report']);
    }

    #[Test]
    public function newPostReported(): void
    {
        $this->models->newPostReported(reportContent:'report');
        $this->assertTrue(Post::query()->whereHas('flags')->exists());
    }

    #[Test]
    public function newPostDeletedReported(): void
    {
        $this->models->newPostDeletedReported(reportContent:'report');
        $this->assertTrue($this->reportPost('report')->trashed());
    }

    private function reportPost(string $reportContent): Post
    {
        /** @var Flag $model */
        $model = Flag::query()->where('text', $reportContent)->first();
        /** @var Post $post */
        $post = $model->posts()->withTrashed()->first();
        return $post;
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function newPostReportedMany(): void
    {
        $this->models->newPostReported();
        $this->models->newPostReported();
    }
}
