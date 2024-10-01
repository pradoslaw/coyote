<?php
namespace Coyote\Domain;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class UniformDates
{
    public function uniformWeeks(string $startDate, string $endDate): array
    {
        return $this->uniformDates(CarbonPeriod::weeks(), $startDate, $endDate);
    }

    public function uniformMonths(string $startDate, string $endDate): array
    {
        return $this->uniformDates(CarbonPeriod::months(), $startDate, $endDate);
    }

    public function uniformYears(string $startDate, string $endDate): array
    {
        return $this->uniformDates(CarbonPeriod::years(), $startDate, $endDate);
    }

    private function uniformDates(CarbonPeriod $period, string $start, string $end): array
    {
        return \iterator_to_array($period
            ->setDates($start, $end)
            ->map(fn(Carbon $carbon) => $carbon->toDateString()));
    }
}
