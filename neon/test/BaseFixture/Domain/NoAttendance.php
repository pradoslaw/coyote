<?php
namespace Neon\Test\BaseFixture\Domain;

use Neon\Domain;
use Neon\Persistence\Attendance;

readonly class NoAttendance implements Attendance
{
    public function fetchAttendance(): Domain\Attendance
    {
        return new Domain\Attendance(0, 0);
    }
}
