<?php
namespace Coyote\Domain;

use Carbon\Carbon;

readonly class DiscreetDate
{
    private Carbon $date;

    public function __construct(string $dateTime)
    {
        $this->date = new Carbon($dateTime);
    }

    public function startOfThisWeek(): string
    {
        return $this->date->startOfWeek()->toDateTimeString();
    }

    public function startOfThisMonth(): string
    {
        return $this->date->startOfMonth()->toDateTimeString();
    }

    public function startOfThisQuarter(): string
    {
        return $this->date->startOfQuarter()->toDateTimeString();
    }
}
