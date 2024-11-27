<?php
namespace Tests\Integration\Registrations;

use Coyote\Domain\Registration\HistoryRange;
use Coyote\Domain\Registration\Period;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HistoryRangeTest extends TestCase
{
    #[Test]
    public function onWednesday_startIsTwoDaysAgo(): void
    {
        $range = new HistoryRange('2024-09-25', Period::Week, 0);
        $this->assertSame('2024-09-23', $range->startDate());
    }

    #[Test]
    public function onTuesday_startIsOneDaysAgo(): void
    {
        $range = new HistoryRange('2024-09-24', Period::Week, 0);
        $this->assertSame('2024-09-23', $range->startDate());
    }

    #[Test]
    public function onMonday_startIsToday(): void
    {
        $range = new HistoryRange('2024-09-23', Period::Week, 0);
        $this->assertSame('2024-09-23', $range->startDate());
    }

    #[Test]
    public function onSunday_startsSixDaysAgo(): void
    {
        $range = new HistoryRange('2024-09-22', Period::Week, 0);
        $this->assertSame('2024-09-16', $range->startDate());
    }

    #[Test]
    public function previousWeek(): void
    {
        $range = new HistoryRange('2024-09-23', Period::Week, 1);
        $this->assertSame('2024-09-16', $range->startDate());

        $range = new HistoryRange('2024-09-23', Period::Week, 2);
        $this->assertSame('2024-09-09', $range->startDate());
    }

    #[Test]
    public function identityEndDate(): void
    {
        $range = new HistoryRange('2024-09-24', Period::Week, 1);
        $this->assertSame('2024-09-24', $range->endDate());
    }

    #[Test]
    public function historyRangeMonths(): void
    {
        $range = new HistoryRange('2024-09-23', Period::Month, 0);
        $this->assertSame('2024-09-01', $range->startDate());
    }

    #[Test]
    public function historyRangeMonthsFirstDay(): void
    {
        $range = new HistoryRange('2024-09-01', Period::Month, 0);
        $this->assertSame('2024-09-01', $range->startDate());
    }

    #[Test]
    public function historyRangeMonthsPreviousMonth(): void
    {
        $range = new HistoryRange('2024-09-23', Period::Month, 1);
        $this->assertSame('2024-08-01', $range->startDate());

        $range = new HistoryRange('2024-09-23', Period::Month, 2);
        $this->assertSame('2024-07-01', $range->startDate());
    }

    #[Test]
    public function doesNotModifyInternalState(): void
    {
        $range = new HistoryRange('2024-09-23', Period::Week, 2);
        $range->startDate();
        $this->assertSame('2024-09-23', $range->endDate());
    }

    #[Test]
    public function historyRangeYearsFirstDay(): void
    {
        $range = new HistoryRange('2024-09-23', Period::Year, 0);
        $this->assertSame('2024-01-01', $range->startDate());
    }

    #[Test]
    public function historyRangeYearsPreviousYear(): void
    {
        $range = new HistoryRange('2023-09-23', Period::Year, 1);
        $this->assertSame('2022-01-01', $range->startDate());
    }

    #[Test]
    public function historyRangeYearsPreviousYearLeapYear(): void
    {
        $range = new HistoryRange('2024-09-23', Period::Year, 1);
        $this->assertSame('2023-01-01', $range->startDate());
    }
}
