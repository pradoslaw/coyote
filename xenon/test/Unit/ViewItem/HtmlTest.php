<?php
namespace Xenon\Test\Unit\ViewItem;

use PHPUnit\Framework\TestCase;
use Xenon\Html;
use Xenon\Tag;
use Xenon\Test\Unit\Fixture;
use Xenon\Xenon;

class HtmlTest extends TestCase
{
    use Fixture;

    private Xenon $xenon;

    /**
     * @before
     */
    public function html(): void
    {
        $this->xenon = new Xenon([new Html('div', '<script></script>')], []);
    }

    /**
     * @test
     */
    public function ssr(): void
    {
        $this->assertHtml($this->xenon, '<div><script></script></div>');
    }

    /**
     * @test
     */
    public function spa(): void
    {
        $this->assertHtmlRuntime($this->xenon, '<div><script></script></div>');
    }
}
