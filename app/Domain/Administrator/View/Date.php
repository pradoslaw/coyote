<?php
namespace Coyote\Domain\Administrator\View;

use Carbon\Carbon;
use Carbon\CarbonInterval;

readonly class Date
{
    public function __construct(private Carbon $date, private Carbon $now)
    {
    }

    public function format(): string
    {
        return $this->date->format('Y-m-d H:i:s');
    }

    public function ago(): string
    {
        return $this->leadingWords($this->interval(), amount:4) . ' temu';
    }

    private function leadingWords(CarbonInterval $interval, int $amount): string
    {
        return \implode(' ',
            \array_slice(
                \explode(' ', $interval),
                0, $amount));
    }

    private function interval(): CarbonInterval
    {
        return $this->date->diff($this->now);
    }

    public function timestamp(): int
    {
        return $this->date->timestamp;
    }
}
