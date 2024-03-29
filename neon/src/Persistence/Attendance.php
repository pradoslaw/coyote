<?php
namespace Neon\Persistence;

use Neon\Domain;

interface Attendance
{
    public function fetchAttendance(): Domain\Attendance;
}
