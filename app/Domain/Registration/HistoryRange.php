<?php
namespace Coyote\Domain\Registration;

use Carbon\CarbonImmutable;

readonly class HistoryRange
{
    private CarbonImmutable $endDate;
    public Period $period;

    public function __construct(
        string       $endDate,
        private ?int $weeks = null,
        private ?int $months = null,
    )
    {
        if ($this->weeks === null && $this->months === null) {
            throw new \Exception('Failed to create history range without period: week,month.');
        }
        $this->period = $this->weeks === null ? Period::Month : Period::Week;
        $this->endDate = CarbonImmutable::parse($endDate);
    }

    public function startDate(): string
    {
        return $this->periodStartDate()->toDateString();
    }

    private function periodStartDate(): CarbonImmutable
    {
        if ($this->months === null) {
            return $this->endDate
                ->subWeeks($this->weeks)
                ->subDays($this->excessWeekDays());
        }
        return $this->endDate
            ->subMonths($this->months)
            ->subDays($this->excessMonthDays());
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
