<?php
namespace Coyote\Domain\Registration;

use Carbon\CarbonImmutable;

readonly class HistoryRange
{
    private CarbonImmutable $endDate;

    public function __construct(string $endDate, public Period $period, private int $value)
    {
        $this->endDate = CarbonImmutable::parse($endDate);
    }

    public function startDate(): string
    {
        return $this->periodStartDate()->toDateString();
    }

    private function periodStartDate(): CarbonImmutable
    {
        return match ($this->period) {
            Period::Day => $this->endDate->subDays($this->value),
            Period::Week => $this->endDate->subDays($this->excessWeekDays())->subWeeks($this->value),
            Period::Month => $this->endDate->subDays($this->excessMonthDays())->subMonths($this->value),
            Period::Year => $this->endDate->subDays($this->excessYearDays())->subYears($this->value),
        };
    }

    private function excessWeekDays(): int
    {
        if ($this->endDate->dayOfWeek === 0) {
            return 6;
        }
        return $this->endDate->dayOfWeek - 1;
    }

    private function excessMonthDays(): int
    {
        return $this->endDate->dayOfMonth - 1;
    }

    private function excessYearDays(): int
    {
        return $this->endDate->dayOfYear - 1;
    }

    public function endDate(): string
    {
        return $this->endDate->toDateString();
    }
}
