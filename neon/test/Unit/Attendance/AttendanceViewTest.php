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
        $this->assertFieldRenderedIn(
            'totalAmount',
            '//*[@id="attendance"]//*[@id="totalAmount"]/text()');
    }

    /**
     * @test
     */
    public function onlineUsers(): void
    {
        $this->assertFieldRenderedIn(
            'onlineAmount',
            '//*[@id="attendance"]//*[@id="onlineAmount"]/text()');
    }

    /**
     * @test
     */
    public function totalUsersTitle(): void
    {
        $this->assertFieldRenderedIn(
            'totalTitle',
            '//*[@id="attendance"]//*[@id="totalTitle"]/text()');
    }

    /**
     * @test
     */
    public function onlineUsersTitle(): void
    {
        $this->assertFieldRenderedIn(
            'onlineTitle',
            '//*[@id="attendance"]//*[@id="onlineTitle"]/text()');
    }

    private function assertFieldRenderedIn(string $fieldName, string $xPath): void
    {
        $this->assertSame('magic value', $this
            ->view([$fieldName => 'magic value'])
            ->find($xPath));
    }

    private function view(array $fields): ViewDom
    {
        $view = new Neon\View([], [
            new Neon\View\Attendance(
                $fields['totalAmount'] ?? '',
                $fields['onlineAmount'] ?? '',
                $fields['totalTitle'] ?? '',
                $fields['onlineTitle'] ?? '',
            ),
        ]);
        return new ViewDom($view->html());
    }
}
