<?php
namespace Coyote\Domain;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Coyote\Domain\Registration\Period;

class UniformDates
{
    public function uniform(Period $period, string $startDate, string $endDate): array
    {
        return $this->uniformDates($this->carbonPeriod($period), $startDate, $endDate);
    }

    private function uniformDates(CarbonPeriod $period, string $start, string $end): array
    {
        return \iterator_to_array($period
            ->setDates($start, $end)
            ->map(fn(Carbon $carbon) => $carbon->toDateString()));
    }

    private function carbonPeriod(Period $period): CarbonPeriod
    {
        return match ($period) {
            Period::Week => CarbonPeriod::weeks(),
            Period::Month => CarbonPeriod::months(),
            Period::Year => CarbonPeriod::years(),
        };
    }
}
