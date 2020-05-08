<?php

namespace Coyote\Services\Helper;

use Carbon\Carbon;

class DateDifference
{
    /** @var string */
    private $format;
    /** @var bool */
    private $diffForHumans;

    public function __construct(string $format, bool $diffForHumans)
    {
        $this->format = $format;
        $this->diffForHumans = $diffForHumans;
    }

    public function format(Carbon $earlier): string
    {
        if ($this->diffForHumans) {
            return $this->diffForHumans($earlier);
        }
        return $earlier->formatLocalized($this->format);
    }

    private function diffForHumans(Carbon $earlier): string
    {
        if ($earlier->diffInHours() < 1) {
            return $earlier->diffForHumans(null, true) . ' temu';
        }
        if ($earlier->isToday()) {
            return 'dziś, ' . $earlier->format('H:i');
        }
        if ($earlier->isYesterday()) {
            return 'wczoraj, ' . $earlier->format('H:i');
        }
        return $earlier->formatLocalized($this->format);
    }
}
