<?php
namespace Neon\View\ViewModel;

use Neon\Domain;
use Neon\View\Language\Language;

class Attendance
{
    public string $totalUsers;
    public string $onlineUsers;
    public string $totalUsersTitle;
    public string $onlineUsersTitle = 'Online';

    public function __construct(Language $lang, Domain\Attendance $attendance)
    {
        $this->totalUsers = \number_format($attendance->totalUsers, thousands_separator:'.');
        $this->onlineUsers = \number_format($attendance->onlineUsers, thousands_separator:'.');
        $this->totalUsersTitle = $lang->t('Users');
    }
}
