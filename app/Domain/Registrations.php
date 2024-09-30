<?php
namespace Coyote\Domain;

use Coyote\User;

class Registrations
{
    public function registrations(string $from, string $to): array
    {
        $query = User::withTrashed();

        $query
            ->where('created_at', '>=', "$from 00:00:00")
            ->where('created_at', '<=', "$to 24:00:00");

        $collection = $query
            ->selectRaw("date_trunc('week', created_at) as created_at_week, Count(*) AS count")
            ->groupByRaw("date_trunc('week', created_at)")
            ->get(['created_at_week', 'total']);

        $array = $collection->toArray();
        return \array_combine(
            \array_map(
                $this->datetimeToDate(...),
                \array_column($array, 'created_at_week')),
            \array_column($array, 'count'),
        );
    }

    private function datetimeToDate(string $datetime): string
    {
        return \strStr($datetime, ' ', before_needle:true);
    }
}
