<?php
namespace Coyote\Domain;

use Illuminate\Database\Connection;

class RouteVisits
{
    public function __construct(private Connection $connection)
    {
    }

    public function visit(string $path, string $date): void
    {
        $visits = $this->connection->table('route_visits');
        $query = $visits->where([
            'path' => $path,
            'date' => $date,
        ]);
        if ($query->exists()) {
            $query->update([
                'visits' => $this->connection->raw('visits + 1'),
            ]);
        } else {
            $visits->insert([
                'path'   => $path,
                'date'   => $date,
                'visits' => 1,
            ]);
        }
    }
}
