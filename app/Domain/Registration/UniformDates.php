<?php
namespace Coyote\Domain\Registration;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class UniformDates
{
    public function inRange(HistoryRange $range): array
    {
        return \iterator_to_array($this->carbonPeriod($range->period)
            ->setDates($range->startDate(), $range->endDate())
            ->map(fn(Carbon $carbon) => $carbon->toDateString()));
    }

    private function carbonPeriod(Period $period): CarbonPeriod
    {
        return match ($period) {
            Period::Day => CarbonPeriod::days(),
            Period::Week => CarbonPeriod::weeks(),
            Period::Month => CarbonPeriod::months(),
            Period::Year => CarbonPeriod::years(),
        };
    }
}
