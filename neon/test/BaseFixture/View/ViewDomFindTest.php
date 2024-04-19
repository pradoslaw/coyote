<?php
namespace Neon\Test\BaseFixture\View;

use PHPUnit\Framework\TestCase;
use function Neon\Test\BaseFixture\Caught\caught;

class ViewDomFindTest extends TestCase
{
    /**
     * @test
     */
    public function textContent(): void
    {
        $dom = new ViewDom('<ul><li>Winter is coming</li><ul>');
        $this->assertSame('Winter is coming', $dom->findString('/html/body/ul/li/text()'));
    }

    /**
     * @test
     */
    public function notFound(): void
    {
        $dom = new ViewDom('<p>Missing</p>');
        $throwable = caught(fn() => $dom->findString('/html/body/ul/li'));
        $this->assertStringStartsWith('Failed to find element: /html/body/ul/li', $throwable->getMessage());
    }

    /**
     * @test
     */
    public function many(): void
    {
        $dom = new ViewDom('<p>One</p><p>Two</p>');
        $throwable = caught(fn() => $dom->findString('//p'));
        $this->assertStringStartsWith('Failed to find unique element (found 2): //p', $throwable->getMessage());
    }

    /**
     * @test
     */
    public function textNode(): void
    {
        $dom = new ViewDom('<p>We do not sow</p>');
        $this->assertSame('We do not sow', $dom->findString('//p/text()'));
    }

    /**
     * @test
     */
    public function htmlEntity(): void
    {
        $dom = new ViewDom('<p>&gt;</p>');
        $this->assertSame('>', $dom->findString('//p/text()'));
    }

    /**
     * @test
     */
    public function throwForElement(): void
    {
        $dom = new ViewDom('<ul></ul>');
        $exception = caught(fn() => $dom->findString('/html/body/ul'));
        $this->assertSame('Failed to get element as string: <ul>', $exception->getMessage());
    }

    /**
     * @test
     */
    public function attribute(): void
    {
        $dom = new ViewDom('<a href="foo"></a>');
        $attribute = $dom->findString('/html/body/a/@href');
        $this->assertSame('foo', $attribute);
    }
}
