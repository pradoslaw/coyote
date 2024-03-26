<?php
namespace Neon\Test\Unit\Attendance;

use Neon\Domain;
use Neon\View\ViewModel\Attendance;
use PHPUnit\Framework\TestCase;

class AttendanceViewModelTest extends TestCase
{
    /**
     * @test
     */
    public function totalUsers(): void
    {
        $attendance = new Attendance(new Domain\Attendance(123, 0));
        $this->assertSame('123', $attendance->totalUsers);
    }

    /**
     * @test
     */
    public function totalUsersThousandSeparator(): void
    {
        $attendance = new Attendance(new Domain\Attendance(1234, 0));
        $this->assertSame('1.234', $attendance->totalUsers);
    }

    /**
     * @test
     */
    public function onlineUsers(): void
    {
        $attendance = new Attendance(new Domain\Attendance(0, 456));
        $this->assertSame('456', $attendance->onlineUsers);
    }

    /**
     * @test
     */
    public function onlineUsersThousandSeparator(): void
    {
        $attendance = new Attendance(new Domain\Attendance(0, 4567));
        $this->assertSame('4.567', $attendance->onlineUsers);
    }
}
