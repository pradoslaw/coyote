<?php
namespace Coyote\Domain;

use Carbon\Carbon;

readonly class HistoryRange
{
    public function __construct(
        private string $endDate,
        private int    $weeks,
    )
    {
    }

    public function startDate(): string
    {
        $endDate = new Carbon($this->endDate);
        return $endDate
            ->subWeeks($this->weeks)
            ->subDays($this->excessDays($endDate))
            ->toDateString();
    }

    private function excessDays(Carbon $startDate): int
    {
        $dayOfWeek = $startDate->dayOfWeek;
        if ($dayOfWeek === 0) {
            return 6;
        }
        return $dayOfWeek - 1;
    }

    public function endDate(): string
    {
        return $this->endDate;
    }
}
