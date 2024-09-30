<?php
namespace Tests\Unit\Registrations;

use Coyote\Domain\HistoryRange;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HistoryRangeTest extends TestCase
{
    #[Test]
    public function onWednesday_startIsTwoDaysAgo(): void
    {
        $range = new HistoryRange('2024-09-25', 0);
        $this->assertSame('2024-09-23', $range->startDate());
    }

    #[Test]
    public function onTuesday_startIsOneDaysAgo(): void
    {
        $range = new HistoryRange('2024-09-24', 0);
        $this->assertSame('2024-09-23', $range->startDate());
    }

    #[Test]
    public function onMonday_startIsToday(): void
    {
        $range = new HistoryRange('2024-09-23', 0);
        $this->assertSame('2024-09-23', $range->startDate());
    }

    #[Test]
    public function onSunday_startsSixDaysAgo(): void
    {
        $range = new HistoryRange('2024-09-22', 0);
        $this->assertSame('2024-09-16', $range->startDate());
    }

    #[Test]
    public function previousWeek(): void
    {
        $range = new HistoryRange('2024-09-23', 1);
        $this->assertSame('2024-09-16', $range->startDate());

        $range = new HistoryRange('2024-09-23', 2);
        $this->assertSame('2024-09-09', $range->startDate());
    }

    #[Test]
    public function identityEndDate(): void
    {
        $range = new HistoryRange('2024-09-24', 1);
        $this->assertSame('2024-09-24', $range->endDate());
    }
}
