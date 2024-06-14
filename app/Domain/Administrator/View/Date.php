<?php
namespace Coyote\Domain\Administrator\View;

use Carbon\Carbon;
use Carbon\CarbonInterval;

readonly class Date
{
    public function __construct(private Carbon $date, private Carbon $now)
    {
    }

    public function __toString(): string
    {
        return $this->format();
    }

    public function format(): string
    {
        return $this->date->format('Y-m-d H:i:s');
    }

    public function timestamp(): int
    {
        return $this->date->timestamp;
    }

    public function ago(): string
    {
        return $this->firstWords($this->interval(), 4) . ' temu';
    }

    private function firstWords(CarbonInterval $interval, int $words): string
    {
        return \implode(' ',
            \array_slice(
                \explode(' ', $interval),
                0, $words));
    }

    private function interval(): CarbonInterval
    {
        return $this->date->diff($this->now);
    }
}
