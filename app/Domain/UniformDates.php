<?php
namespace Coyote\Domain;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class UniformDates
{
    public function uniformWeeks(string $startDate, string $endDate): array
    {
        return \iterator_to_array(CarbonPeriod::weeks()
            ->setDates($startDate, $endDate)
            ->map(fn(Carbon $carbon) => $carbon->toDateString()));
    }
}
