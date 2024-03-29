<?php
namespace Neon\Test\BaseFixture;

use Neon\Domain;
use Neon\Persistence\Attendance;

readonly class NoneAttendance implements Attendance
{
    public function fetchAttendance(): Domain\Attendance
    {
        return new Domain\Attendance(0, 0);
    }
}
