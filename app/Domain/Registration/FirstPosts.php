<?php
namespace Coyote\Domain\Registration;

use Coyote\Post;
use Illuminate\Support\Facades\DB;

readonly class FirstPosts implements ChartSource
{
    private UniformDates $uniformDates;

    public function __construct()
    {
        $this->uniformDates = new UniformDates();
    }

    public function id(): string
    {
        return 'firstPost.createdAt';
    }

    public function title(): string
    {
        return 'Pierwszy post użytkownika';
    }

    public function inRange(HistoryRange $range): array
    {
        return \array_merge(
            $this->arrayFrom(
                keys:$this->uniformDates->inRange($range),
                value:0),
            $this->fetchRegistrationsByPeriod($range->startDate(), $range->endDate(), $range->period));
    }

    private function fetchRegistrationsByPeriod(string $from, string $to, Period $period): array
    {
        $dateTruncSqlField = $this->dateTruncSqlField('first_post_date', $period);
        return DB::query()
            ->from(Post::query()
                ->select('user_id', DB::raw('MIN(created_at)::date as first_post_date'))
                ->groupBy('user_id')
                ->havingRaw("MIN(created_at) >= ?", ["$from 00:00:00"])
                ->havingRaw("MIN(created_at) < ?", ["$to 24:00:00"]),
                as:'firstPosts')
            ->selectRaw("$dateTruncSqlField as created_at_group, Count(*) AS count")
            ->groupByRaw($dateTruncSqlField)
            ->pluck('count', 'created_at_group')
            ->toArray();
    }

    private function dateTruncSqlField(string $column, Period $period): string
    {
        return match ($period) {
            Period::Day => "date_trunc('day', $column)::date",
            Period::Week => "date_trunc('week', $column)::date",
            Period::Month => "date_trunc('month', $column)::date"
        };
    }

    private function arrayFrom(array $keys, int $value): array
    {
        $values = \array_fill(0, \count($keys), $value);
        return \array_combine($keys, $values);
    }
}
