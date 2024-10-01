<?php
namespace Tests\Unit\Registrations;

use Coyote\Domain\Registration\Period;
use Coyote\Domain\UniformDates;
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
        $mondayFirst = '2124-09-04';
        $mondayThird = '2124-09-18';
        $this->assertSame([
            '2124-09-04',
            '2124-09-11',
            '2124-09-18',
        ],
            $this->dates->uniform(Period::Week, $mondayFirst, $mondayThird));
    }

    #[Test]
    public function sameWeek(): void
    {
        $mondayFirst = '2124-09-04';
        $mondayThird = '2124-09-05';
        $this->assertSame(['2124-09-04'],
            $this->dates->uniform(Period::Week, $mondayFirst, $mondayThird));
    }

    #[Test]
    public function fillMonthDates(): void
    {
        $this->assertSame([
            '2124-07-04',
            '2124-08-04',
            '2124-09-04',
        ],
            $this->dates->uniform(Period::Month, '2124-07-04', '2124-09-04'));
    }

    #[Test]
    public function fillYearDates(): void
    {
        $this->assertSame([
            '2122-01-02',
            '2123-01-02',
            '2124-01-02',
        ],
            $this->dates->uniform(Period::Year, '2122-01-02', '2124-03-04'));
    }
}
