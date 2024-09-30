<?php
namespace Tests\Unit\Registrations;

use Coyote\Domain\Registrations;
use Coyote\Post;
use Coyote\User;
use Coyote\Wiki\Log;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class RegistrationsTest extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use BaseFixture\Forum\Models;

    private Registrations $registrations;

    #[Before]
    public function initialize(): void
    {
        $this->registrations = new Registrations();
    }

    #[Before]
    public function removeUsers(): void
    {
        Log::query()->forceDelete();
        Post::query()->forceDelete();
        User::query()->forceDelete();
    }

    #[Test]
    public function noRegistrations(): void
    {
        $this->assertEmpty($this->registrations('2126-01-23', '2126-01-23'));
    }

    #[Test]
    public function oneRegistration(): void
    {
        $this->models->newUser(createdAt:'2125-01-23 21:37:00');
        $this->assertCount(1, $this->registrations('2125-01-22', '2125-01-24'));
    }

    #[Test]
    public function includeDateEdgeStart(): void
    {
        $this->models->newUser(createdAt:'2125-01-23 21:37:00');
        $this->assertCount(1, $this->registrations('2125-01-23', '2125-01-24'));
    }

    #[Test]
    public function includeDateEdgeEnd(): void
    {
        $this->models->newUser(createdAt:'2125-01-23 21:37:00');
        $this->assertCount(1, $this->registrations('2125-01-22', '2125-01-23'));
    }

    #[Test]
    public function includeDateEdgeEndLastSecondOfTheDay(): void
    {
        $this->models->newUser(createdAt:'2125-01-23 23:59:59');
        $this->assertCount(1, $this->registrations('2125-01-22', '2125-01-23'));
    }

    #[Test]
    public function countUsersOne(): void
    {
        $this->models->newUser(createdAt:'2024-09-30 21:37:00');
        $this->assertSame([1], $this->registrations('2024-09-30', '2024-10-01'));
    }

    #[Test]
    public function countUsersGroupByWeek(): void
    {
        $sunday = '2024-09-29 21:37:00';
        $monday = '2024-09-30 21:37:00';
        $this->models->newUser(createdAt:$sunday);
        $this->models->newUser(createdAt:$monday);
        $this->assertSame([1, 1], $this->registrations('2024-09-29', '2024-09-30'));
    }

    #[Test]
    public function countUsersInSameWeek(): void
    {
        $monday = '2024-09-30 21:37:00';
        $tuesday = '2024-10-01 21:37:00';
        $this->models->newUser(createdAt:$monday);
        $this->models->newUser(createdAt:$tuesday);
        $this->assertSame([2], $this->registrations('2024-09-30', '2024-10-01'));
    }

    #[Test]
    public function includeUsersWhoAreDeleted(): void
    {
        $this->models->newUser(createdAt:'2125-01-23 21:37:00', deleted:true);
        $this->assertCount(1, $this->registrations('2125-01-22', '2125-01-24'));
    }

    #[Test]
    public function weekDatesAsArrayKeys(): void
    {
        $monday = "2024-09-30";
        $tuesday = "2024-10-01";
        $this->models->newUser(createdAt:"$tuesday 21:37:00", deleted:true);
        $this->assertSame(
            [$monday => 1],
            $this->registrations->registrations('2024-10-01', '2024-10-01'));
    }

    private function registrations(string $from, string $to): array
    {
        return \array_values($this->registrations->registrations($from, $to));
    }
}
