<?php
namespace Tests\Legacy\IntegrationNew\Registrations;

use Coyote\Domain\Registration\HistoryRange;
use Coyote\Domain\Registration\Period;
use Coyote\Domain\Registration\UniformDates;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UniformDatesTest extends TestCase
{
    private UniformDates $dates;

    #[Before]
    public function initialize(): void
    {
        $this->dates = new UniformDates();
    }

    #[Test]
    public function fillWeekDates(): void
    {
        $this->assertRangeDates([
            '2124-09-04',
            '2124-09-11',
            '2124-09-18',
        ], new HistoryRange('2124-09-18', Period::Week, 2));
    }

    #[Test]
    public function sameWeek(): void
    {
        $this->assertRangeDates(['2124-09-04'], new HistoryRange('2124-09-05', Period::Week, 0));
    }

    #[Test]
    public function fillMonthDates(): void
    {
        $this->assertRangeDates([
            '2124-07-01',
            '2124-08-01',
            '2124-09-01',
        ], new HistoryRange('2124-09-04', Period::Month, 2));
    }

    #[Test]
    public function fillYearDates(): void
    {
        $this->assertRangeDates([
            '2122-01-01',
            '2123-01-01',
            '2124-01-01',
        ], new HistoryRange('2124-03-04', Period::Year, 2));
    }

    private function assertRangeDates(array $expectedRange, HistoryRange $range): void
    {
        $this->assertSame($expectedRange,
            $this->dates->inRange($range));
    }
}
