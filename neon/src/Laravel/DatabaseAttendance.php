<?php
namespace Neon\Laravel;

use Illuminate\Database\DatabaseManager;
use Neon\Domain;
use Neon\Persistence\Attendance;

readonly class DatabaseAttendance implements Attendance
{
    public function __construct(private DatabaseManager $database)
    {
    }

    public function fetchAttendance(): Domain\Attendance
    {
        $count = $this->database->query()->from('users')->count();
        $online = \max($this->database->query()->from('sessions')
            ->where('robot', '') // TODO this is untested
            ->count(), 1);
        return new Domain\Attendance($count, $online);
    }
}
