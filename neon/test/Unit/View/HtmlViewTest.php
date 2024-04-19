<?php
namespace Neon\Test\Unit\View;

use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View\Html\Head\Title;
use Neon\View\Html\Render;
use Neon\View\Html\Tag;
use Neon\View\HtmlView;
use PHPUnit\Framework\TestCase;

class HtmlViewTest extends TestCase
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
            $dom->findString('/html/head/title/text()'));
    }

    /**
     * @test
     */
    public function unicode(): void
    {
        $dom = new ViewDom($this->viewHtml('€ść'));
        $this->assertSame(
            '€ść',
            $dom->findString('/html/head/title/text()'));
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

    /**
     * @test
     */
    public function plainText(): void
    {
        $this->assertSame(
            '<span>&lt;foo&gt;</span>',
            $this->rendered(new Render(), '<foo>'));
    }

    /**
     * @test
     */
    public function htmlLiteral(): void
    {
        $h = new Render();
        $this->assertSame(
            '<span><foo></span>',
            $this->rendered($h, $h->html('<foo>')));
    }

    private function viewHtml(string $title): string
    {
        return (new HtmlView([new Title($title)], []))->html();
    }

    private function rendered(Render $h, string|Tag $str): string
    {
        return $h->tag('span', [], [$str])->html();
    }
}
