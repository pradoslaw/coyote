<?php
namespace Tests\Unit\Registrations;

use Coyote\Domain\UniformDates;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UniformDatesTest extends TestCase
{
    #[Test]
    public function fillWeekDates(): void
    {
        $mondayFirst = '2124-09-04';
        $mondayThird = '2124-09-18';
        $this->assertSame([
            '2124-09-04',
            '2124-09-11',
            '2124-09-18',
        ],
            $this->uniformWeeks($mondayFirst, $mondayThird));
    }

    #[Test]
    public function sameWeek(): void
    {
        $mondayFirst = '2124-09-04';
        $mondayThird = '2124-09-05';
        $this->assertSame(['2124-09-04'],
            $this->uniformWeeks($mondayFirst, $mondayThird));
    }

    #[Test]
    public function fillMonthDates(): void
    {
        $this->assertSame([
            '2124-07-04',
            '2124-08-04',
            '2124-09-04',
        ],
            $this->uniformMonths('2124-07-04', '2124-09-04'));
    }

    private function uniformWeeks(string $startDate, string $endDate): array
    {
        return (new UniformDates())->uniformWeeks($startDate, $endDate);
    }

    private function uniformMonths(string $startDate, string $endDate): array
    {
        return (new UniformDates())->uniformMonths($startDate, $endDate);
    }
}
