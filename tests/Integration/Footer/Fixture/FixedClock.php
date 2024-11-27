<?php
namespace Tests\Integration\Footer\Fixture;

use Coyote\Domain\Clock;

class FixedClock extends Clock
{
    private int $year = 0;
    private float $executionTime = 0;

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function setExecutionTime(float $seconds): void
    {
        $this->executionTime = $seconds;
    }

    public function year(): int
    {
        return $this->year;
    }

    public function executionTime(): float
    {
        return $this->executionTime;
    }
}
