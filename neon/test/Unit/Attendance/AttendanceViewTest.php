<?php
namespace Neon\Test\Unit\Attendance;

use Neon;
use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\TestCase;

class AttendanceViewTest extends TestCase
{
    /**
     * @test
     */
    public function totalUsers(): void
    {
        $this->assertSame('14', $this
            ->view(['totalAmount' => 14])
            ->find('//*[@id="attendance"]//*[@id="totalAmount"]/text()'));
    }

    /**
     * @test
     */
    public function onlineUsers(): void
    {
        $this->assertSame('54', $this
            ->view(['onlineAmount' => 54])
            ->find('//*[@id="attendance"]//*[@id="onlineAmount"]/text()'));
    }

    /**
     * @test
     */
    public function totalUsersTitle(): void
    {
        $this->assertSame('Users', $this->view([])
            ->find('//*[@id="attendance"]//*[@id="totalTitle"]/text()'));
    }

    /**
     * @test
     */
    public function onlineUsersTitle(): void
    {
        $this->assertSame('Online', $this->view([])
            ->find('//*[@id="attendance"]//*[@id="onlineTitle"]/text()'));
    }

    private function view(array $fields): ViewDom
    {
        $view = new Neon\View\HtmlView([], [
            new Neon\View\Html\Body\Attendance(
                new Neon\View\ViewModel\Attendance(
                    new Neon\View\Language\English(),
                    new Neon\Domain\Attendance(
                        $fields['totalAmount'] ?? 0,
                            $fields['onlineAmount'] ?? 0)),
            ),
        ]);
        return new ViewDom($view->html());
    }
}
