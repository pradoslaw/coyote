<?php
namespace Coyote\Domain;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Coyote\User;

readonly class UserRegistrations
{
    private UniformWeeks $weeks;

    public function __construct()
    {
        $this->weeks = new UniformWeeks();
    }

    public function inRange(HistoryRange $range): array
    {
        return $this->inWeeks($range->startDate(), $range->endDate());
    }

    public function inWeeks(string $startDate, string $endDate): array
    {
        if ($this->isStartOfWeek($startDate)) {
            return $this->registrationsByWeekDates($startDate, $endDate);
        }
        throw new \InvalidArgumentException('Invalid starting boundary, can only start on monday.');
    }

    private function isStartOfWeek(string $date): bool
    {
        $carbon = new Carbon($date);
        return $carbon->dayOfWeek === CarbonInterface::MONDAY;
    }

    private function registrationsByWeekDates(string $startDate, string $endDate): array
    {
        return $this->fillEmptyWeekDates(
            $startDate,
            $endDate,
            $this->fetchRegistrationsByWeekDates($startDate, $endDate));
    }

    private function fetchRegistrationsByWeekDates(string $from, string $to): array
    {
        return User::withTrashed()
            ->where('created_at', '>=', "$from 00:00:00")
            ->where('created_at', '<', "$to 24:00:00")
            ->selectRaw("date_trunc('week', created_at)::date as created_at_week, Count(*) AS count")
            ->groupByRaw("date_trunc('week', created_at)")
            ->get()
            ->pluck(key:'created_at_week', value:'count')
            ->toArray();
    }

    private function fillEmptyWeekDates(string $startDate, string $endDate, array $registrationWeekDates): array
    {
        return \array_merge(
            $this->emptyWeekDates($startDate, $endDate),
            $registrationWeekDates,
        );
    }

    private function emptyWeekDates(string $from, string $to): array
    {
        return $this->createArray(
            keys:$this->weeks->uniformDates($from, $to),
            value:0);
    }

    private function createArray(array $keys, int $value): array
    {
        $values = \array_fill(0, \count($keys), $value);
        return \array_combine($keys, $values);
    }
}
