<?php
namespace Neon\View\ViewModel;

use Neon\Domain;

class Attendance
{
    public string $totalUsers;
    public string $onlineUsers;

    public function __construct(Domain\Attendance $attendance)
    {
        $this->totalUsers = \number_format($attendance->totalUsers, thousands_separator:'.');
        $this->onlineUsers = \number_format($attendance->onlineUsers, thousands_separator:'.');
    }
}
