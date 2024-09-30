<?php
namespace Tests\Unit\Registrations;

use Coyote\Domain\UniformWeeks;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UniformWeeksTest extends TestCase
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
            $this->uniformDates($mondayFirst, $mondayThird));
    }

    #[Test]
    public function sameWeek(): void
    {
        $mondayFirst = '2124-09-04';
        $mondayThird = '2124-09-05';
        $this->assertSame(['2124-09-04'],
            $this->uniformDates($mondayFirst, $mondayThird));
    }

    private function uniformDates(string $startDate, string $endDate): array
    {
        return (new UniformWeeks())->uniformDates($startDate, $endDate);
    }
}
