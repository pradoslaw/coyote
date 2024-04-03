<?php
namespace Neon\Test\Unit\Attendance;

use Neon\Domain;
use Neon\Test\BaseFixture\ItemView;
use Neon\View\Html\Body;
use Neon\View\Language\English;
use Neon\View\ViewModel;
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
            $view->find('#attendance', '#totalAmount'));
    }

    /**
     * @test
     */
    public function onlineUsers(): void
    {
        $view = $this->attendance(['onlineAmount' => 54]);
        $this->assertSame(
            '54',
            $view->find('#attendance', '#onlineAmount'));
    }

    /**
     * @test
     */
    public function totalUsersTitle(): void
    {
        $view = $this->attendance([]);
        $this->assertSame(
            'Users',
            $view->find('#attendance', '#totalTitle'));
    }

    /**
     * @test
     */
    public function onlineUsersTitle(): void
    {
        $view = $this->attendance([]);
        $this->assertSame(
            'Online',
            $view->find('#attendance', '#onlineTitle'));
    }

    private function attendance(array $fields): ItemView
    {
        return new ItemView(
            new Body\Attendance(
                new ViewModel\Attendance(
                    new English(),
                    new Domain\Attendance(
                        $fields['totalAmount'] ?? 0,
                        $fields['onlineAmount'] ?? 0)),
            ));
    }
}
