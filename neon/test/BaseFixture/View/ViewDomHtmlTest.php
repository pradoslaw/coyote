<?php
namespace Neon\Test\BaseFixture\View;

use PHPUnit\Framework\TestCase;
use function Neon\Test\BaseFixture\Caught\caught;

class ViewDomHtmlTest extends TestCase
{
    /**
     * @test
     */
    public function test(): void
    {
        $dom = new ViewDom('<html><body><a href="#foo">Bar</a></body></html>');
        $this->assertSame(
            '<a href="#foo">Bar</a>',
            $dom->html('/html/body/a'),
        );
    }

    /**
     * @test
     */
    public function notFound(): void
    {
        $dom = new ViewDom('<p>Missing</p>');
        $throwable = caught(fn() => $dom->html('/html/body/ul/li'));
        $this->assertStringStartsWith(
            'Failed to find element: /html/body/ul/li',
            $throwable->getMessage());
    }

    /**
     * @test
     */
    public function many(): void
    {
        $dom = new ViewDom('<p>One</p><p>Two</p>');
        $throwable = caught(fn() => $dom->html('//p'));
        $this->assertStringStartsWith(
            'Failed to find unique element (found 2): //p',
            $throwable->getMessage());
    }

    /**
     * @test
     */
    public function text(): void
    {
        $dom = new ViewDom('<p>Foo</p');
        $this->assertSame('Foo', $dom->html('//p/text()'));
    }
}
