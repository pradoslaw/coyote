<?php
namespace Tests\Unit\Footer\Fixture;

use Coyote\Domain\Clock;

class FixedClock extends Clock
{
    private int $year;

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function year(): int
    {
        return $this->year;
    }
}
