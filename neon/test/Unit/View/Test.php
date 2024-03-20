<?php
namespace Neon\Test\Unit\View;

use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    /**
     * @test
     */
    public function docType(): void
    {
        $dom = new ViewDom($this->viewHtml(''));
        $this->assertSame('<!DOCTYPE html>', $dom->docType());
    }

    /**
     * @test
     */
    public function title(): void
    {
        $dom = new ViewDom($this->viewHtml('Winter is coming'));
        $this->assertSame(
            'Winter is coming',
            $dom->find('/html/head/title/text()'));
    }

    /**
     * @test
     */
    public function unicode(): void
    {
        $dom = new ViewDom($this->viewHtml('€ść'));
        $this->assertSame(
            '€ść',
            $dom->find('/html/head/title/text()'));
    }

    /**
     * @test
     */
    public function unicodeHtml(): void
    {
        $this->assertStringContainsString(
            'Kraków',
            $this->viewHtml('Kraków'));
    }

    private function viewHtml(string $title): string
    {
        return (new View($title, []))->html();
    }
}
