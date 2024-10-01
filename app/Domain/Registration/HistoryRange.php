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
            Period::Week => $this->endDate->subWeeks($this->value)->subDays($this->excessWeekDays()),
            Period::Month => $this->endDate->subMonths($this->value)->subDays($this->excessMonthDays())
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

    public function endDate(): string
    {
        return $this->endDate->toDateString();
    }
}
