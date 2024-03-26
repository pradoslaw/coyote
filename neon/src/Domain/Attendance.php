<?php
namespace Neon\Domain;

readonly class Attendance
{
    public function __construct(public int $totalUsers, public int $onlineUsers)
    {
    }
}
