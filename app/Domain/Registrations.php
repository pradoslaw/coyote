<?php
namespace Coyote\Domain;

use Coyote\User;

class Registrations
{
    public function registrations(string $from, string $to): array
    {
        $query = User::query();

        $query
            ->where('created_at', '>=', "$from 00:00:00")
            ->where('created_at', '<=', "$to 24:00:00");

        $collection = $query
            ->selectRaw("date_trunc('week', created_at) as created_at_week, Count(*) AS count")
            ->groupByRaw("date_trunc('week', created_at)")
            ->get(['created_at_week', 'total']);

        return \array_column($collection->toArray(), 'count');
    }
}
