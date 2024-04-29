<?php
namespace Neon\Test\Unit\Attendance;

use Neon\Domain;
use Neon\Test\BaseFixture\ItemView;
use Neon\View\Language\English;
use Neon\View\Theme;
use PHPUnit\Framework\TestCase;

class AttendanceViewTest extends TestCase
{
    /**
     * @test
     */
    public function totalUsers(): void
    {
        $view = $this->attendance(['totalAmount' => 14]);
        $this->assertSame(
            '14',
            $view->findText('#attendance', '#totalAmount'));
    }

    /**
     * @test
     */
    public function onlineUsers(): void
    {
        $view = $this->attendance(['onlineAmount' => 54]);
        $this->assertSame(
            '54',
            $view->findText('#attendance', '#onlineAmount'));
    }

    /**
     * @test
     */
    public function totalUsersTitle(): void
    {
        $view = $this->attendance([]);
        $this->assertSame(
            'Users',
            $view->findText('#attendance', '#totalTitle'));
    }

    /**
     * @test
     */
    public function onlineUsersTitle(): void
    {
        $view = $this->attendance([]);
        $this->assertSame(
            'Online',
            $view->findText('#attendance', '#onlineTitle'));
    }

    private function attendance(array $fields): ItemView
    {
        return new ItemView(
            new \Neon\View\Components\Attendance\AttendanceHtml(
                new \Neon\View\Components\Attendance\Attendance(
                    new English(),
                    new Domain\Attendance(
                        $fields['totalAmount'] ?? 0,
                        $fields['onlineAmount'] ?? 0)),
                new Theme(false),
            ));
    }
}
