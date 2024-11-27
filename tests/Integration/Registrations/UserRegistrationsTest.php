<?php
namespace Tests\Integration\Registrations;

use Coyote\Domain\Registration\HistoryRange;
use Coyote\Domain\Registration\Period;
use Coyote\Domain\Registration\UserRegistrations;
use Coyote\Post;
use Coyote\User;
use Coyote\Wiki\Log;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Integration\BaseFixture;

class UserRegistrationsTest extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use BaseFixture\Forum\Models;

    private UserRegistrations $registrations;

    #[Before]
    public function initialize(): void
    {
        $this->registrations = new UserRegistrations();
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
        $this->assertSame([0], $this->registrationsInWeek('2126-01-21', '2126-01-23'));
    }

    #[Test]
    public function oneRegistration(): void
    {
        $this->driver->newUser(createdAt:'2125-01-23 21:37:00');
        $this->assertSame([1], $this->registrationsInWeek('2125-01-22', '2125-01-24'));
    }

    #[Test]
    public function includeDateEdgeStart(): void
    {
        $this->driver->newUser(createdAt:'2125-01-22 00:00:00');
        $this->assertSame([1], $this->registrationsInWeek('2125-01-22', '2125-01-22'));
    }

    #[Test]
    public function includeDateEdgeEnd(): void
    {
        $this->driver->newUser(createdAt:'2125-01-23 21:37:00');
        $this->assertCount(1, $this->registrationsInWeek('2125-01-22', '2125-01-23'));
    }

    #[Test]
    public function includeDateEdgeEndLastSecondOfTheDay(): void
    {
        $this->driver->newUser(createdAt:'2125-01-23 23:59:59');
        $this->assertCount(1, $this->registrationsInWeek('2125-01-22', '2125-01-23'));
    }

    #[Test]
    public function countUsersOne(): void
    {
        $this->driver->newUser(createdAt:'2024-09-30 21:37:00');
        $this->assertSame([1], $this->registrationsInWeek('2024-09-30', '2024-10-01'));
    }

    #[Test]
    public function countUsersGroupByWeek(): void
    {
        $sunday = '2124-09-24 21:37:00';
        $monday = '2124-09-25 21:37:00';
        $this->driver->newUser(createdAt:$sunday);
        $this->driver->newUser(createdAt:$monday);
        $this->assertSame([1, 1], $this->registrations(new HistoryRange('2124-09-25', Period::Week, 1)));
    }

    #[Test]
    public function countUsersInSameWeek(): void
    {
        $monday = '2024-09-30 21:37:00';
        $tuesday = '2024-10-01 21:37:00';
        $this->driver->newUser(createdAt:$monday);
        $this->driver->newUser(createdAt:$tuesday);
        $this->assertSame([2], $this->registrationsInWeek('2024-09-30', '2024-10-01'));
    }

    #[Test]
    public function includeUsersWhoAreDeleted(): void
    {
        $this->driver->newUser(createdAt:'2125-01-23 21:37:00', deleted:true);
        $this->assertCount(1, $this->registrationsInWeek('2125-01-22', '2125-01-24'));
    }

    #[Test]
    public function weekDatesAsArrayKeys(): void
    {
        $monday = "2024-09-30";
        $tuesday = "2024-10-01";
        $this->driver->newUser(createdAt:"$tuesday 21:37:00", deleted:true);
        $this->assertSame(
            [$monday => 1],
            $this->registrations->inRange(new HistoryRange('2024-10-01', Period::Week, 0)));
    }

    #[Test]
    public function fillWeekDatesWith0Users(): void
    {
        $mondayOfTheFirstWeek = "2124-09-04";
        $mondayOfTheSecondWeek = "2124-09-18";
        $this->driver->newUser(createdAt:"$mondayOfTheFirstWeek 21:37:00");
        $this->driver->newUser(createdAt:"$mondayOfTheSecondWeek 21:37:00");
        $this->assertSame(
            [
                $mondayOfTheFirstWeek  => 1,
                '2124-09-11'           => 0,
                $mondayOfTheSecondWeek => 1,
            ],
            $this->registrations->inRange(new HistoryRange('2124-09-18', Period::Week, 2)));
    }

    #[Test]
    public function fillWeekDatesWithoutUsers(): void
    {
        $this->assertSame([
            '2124-09-04' => 0,
            '2124-09-11' => 0,
            '2124-09-18' => 0,
        ],
            $this->registrations->inRange(new HistoryRange('2124-09-18', Period::Week, 2)));
    }

    private function assertArrayKeys(array $expectedKeys, array $actual): void
    {
        $this->assertSame($expectedKeys, \array_keys($actual));
    }

    private function registrationsInWeek(string $from, string $to): array
    {
        $range = new HistoryRange($to, Period::Week, 0);
        if ($range->startDate() === $from) {
            return $this->registrations($range);
        }
        throw new \Exception();
    }

    private function registrations(HistoryRange $range): array
    {
        return \array_values($this->registrations->inRange($range));
    }

    #[Test]
    public function presentMonthDates(): void
    {
        $this->assertArrayKeys(
            ['2024-07-01', '2024-08-01', '2024-09-01'],
            $this->registrations->inRange(new HistoryRange('2024-09-24', Period::Month, 2)));
    }

    #[Test]
    public function weekOfValueZeroShouldNotBeMistakenForNullWeeks(): void
    {
        $this->assertArrayKeys(
            ['2024-09-30'],
            $this->registrations->inRange(new HistoryRange('2024-10-01', Period::Week, 0)));
    }

    #[Test]
    public function countUsersGroupByMonth(): void
    {
        $this->driver->newUser(createdAt:'2124-09-30 21:37:00');
        $this->driver->newUser(createdAt:'2124-10-01 21:37:00');
        $this->assertSame(
            ['2124-09-01' => 1, '2124-10-01' => 1],
            $this->registrations->inRange(new HistoryRange('2124-10-01', Period::Month, 1)));
    }

    #[Test]
    public function countUsersGroupByYear(): void
    {
        $this->driver->newUser(createdAt:'2123-12-31 21:37:00');
        $this->driver->newUser(createdAt:'2124-01-01 21:37:00');
        $this->assertSame(
            ['2123-01-01' => 1, '2124-01-01' => 1],
            $this->registrations->inRange(new HistoryRange('2124-10-01', Period::Year, 1)));
    }
}
