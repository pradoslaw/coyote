<?php
namespace Neon\Test\Unit\Http\Fixture;

use Neon\Domain;

readonly class NoneAttendance implements \Neon\Persistence\Attendance
{
    public function fetchAttendance(): Domain\Attendance
    {
        return new Domain\Attendance(0, 0);
    }
}
