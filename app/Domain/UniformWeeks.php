<?php
namespace Coyote\Domain;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class UniformWeeks
{
    public function uniformDates(string $startDate, string $endDate): array
    {
        return \iterator_to_array(CarbonPeriod::weeks()
            ->setDates($startDate, $endDate)
            ->map(fn(Carbon $carbon) => $carbon->toDateString()));
    }
}
