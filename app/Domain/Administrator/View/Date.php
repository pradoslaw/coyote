<?php
namespace Coyote\Domain\Administrator\View;

use Carbon\Carbon;
use Carbon\CarbonInterval;

class Date
{
    public function __construct(private Carbon $date)
    {
    }

    public function __toString(): string
    {
        return $this->date->format('Y-m-d H:i:s');
    }

    public function timeAgo(): string
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
        return $this->date->diff(Carbon::now());
    }
}
