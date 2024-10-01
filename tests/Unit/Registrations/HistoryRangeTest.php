<?php
namespace Tests\Unit\Registrations;

use Coyote\Domain\Registration\HistoryRange;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HistoryRangeTest extends TestCase
{
    #[Test]
    public function onWednesday_startIsTwoDaysAgo(): void
    {
        $range = new HistoryRange('2024-09-25', weeks:0);
        $this->assertSame('2024-09-23', $range->startDate());
    }

    #[Test]
    public function onTuesday_startIsOneDaysAgo(): void
    {
        $range = new HistoryRange('2024-09-24', weeks:0);
        $this->assertSame('2024-09-23', $range->startDate());
    }

    #[Test]
    public function onMonday_startIsToday(): void
    {
        $range = new HistoryRange('2024-09-23', weeks:0);
        $this->assertSame('2024-09-23', $range->startDate());
    }

    #[Test]
    public function onSunday_startsSixDaysAgo(): void
    {
        $range = new HistoryRange('2024-09-22', weeks:0);
        $this->assertSame('2024-09-16', $range->startDate());
    }

    #[Test]
    public function previousWeek(): void
    {
        $range = new HistoryRange('2024-09-23', weeks:1);
        $this->assertSame('2024-09-16', $range->startDate());

        $range = new HistoryRange('2024-09-23', weeks:2);
        $this->assertSame('2024-09-09', $range->startDate());
    }

    #[Test]
    public function identityEndDate(): void
    {
        $range = new HistoryRange('2024-09-24', weeks:1);
        $this->assertSame('2024-09-24', $range->endDate());
    }

    #[Test]
    public function historyRangeMonths(): void
    {
        $range = new HistoryRange('2024-09-23', months:0);
        $this->assertSame('2024-09-01', $range->startDate());
    }

    #[Test]
    public function historyRangeMonthsFirstDay(): void
    {
        $range = new HistoryRange('2024-09-01', months:0);
        $this->assertSame('2024-09-01', $range->startDate());
    }

    #[Test]
    public function historyRangeMonthsPreviousMonth(): void
    {
        $range = new HistoryRange('2024-09-23', months:1);
        $this->assertSame('2024-08-01', $range->startDate());

        $range = new HistoryRange('2024-09-23', months:2);
        $this->assertSame('2024-07-01', $range->startDate());
    }

    #[Test]
    public function throwForNoPeriod(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to create history range without period: week,month.');
        new HistoryRange('2024-09-23');
    }

    #[Test]
    public function doesNotModifyInternalState(): void
    {
        $range = new HistoryRange('2024-09-23', weeks:2);
        $range->startDate();
        $this->assertSame('2024-09-23', $range->endDate());
    }
}
