<?php
namespace Tests\Unit\Survey;

use Coyote\Domain\Survey\Clock;

class MemoryClock extends Clock
{
    private string $time = '';

    public function time(): string
    {
        return $this->time;
    }

    public function setTime(string $time): void
    {
        $this->time = $time;
    }
}
