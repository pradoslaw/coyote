<?php
namespace Neon\Persistence;

use Illuminate\Database\DatabaseManager;
use Neon\Domain;

readonly class DatabaseAttendance implements Attendance
{
    public function __construct(private DatabaseManager $database)
    {
    }

    public function fetchAttendance(): Domain\Attendance
    {
        $count = $this->database->query()->from('users')->count();
        $online = $this->database->query()->from('sessions')->count();
        return new Domain\Attendance($count, $online);
    }
}
