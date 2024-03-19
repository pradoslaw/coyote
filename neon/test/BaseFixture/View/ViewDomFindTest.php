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
        $this->assertSame('Winter is coming', $dom->find('/html/body/ul/li'));
    }

    /**
     * @test
     */
    public function notFound(): void
    {
        $dom = new ViewDom('<p>Missing</p>');
        $throwable = caught(fn() => $dom->find('/html/body/ul/li'));
        $this->assertStringStartsWith('Failed to find element: /html/body/ul/li', $throwable->getMessage());
    }

    /**
     * @test
     */
    public function many(): void
    {
        $dom = new ViewDom('<p>One</p><p>Two</p>');
        $throwable = caught(fn() => $dom->find('//p'));
        $this->assertStringStartsWith('Failed to find unique element (found 2): //p', $throwable->getMessage());
    }

    /**
     * @test
     */
    public function textNode(): void
    {
        $dom = new ViewDom('<p>We do not sow</p>');
        $this->assertSame('We do not sow', $dom->find('//p/text()'));
    }

    /**
     * @test
     */
    public function htmlEntity(): void
    {
        $dom = new ViewDom('<p>&gt;</p>');
        $this->assertSame('>', $dom->find('//p/text()'));
    }

    /**
     * @test
     */
    public function structureNotFound(): void
    {
        $throwable = $this->listItemException('<title>Foo</title> <p>Bar</p>');
        $this->assertStringEndsWith(
            'html(head(title),body(p))',
            $throwable->getMessage());
    }

    /**
     * @test
     */
    public function structureMany(): void
    {
        $throwable = $this->listItemException('<ul><li>One</li><li>Two</li></ul>');
        $this->assertStringEndsWith(
            'html(body(ul(li,li)))',
            $throwable->getMessage());
    }

    private function listItemException(string $html): \Throwable
    {
        return caught(fn() => (new ViewDom($html))->find('/html/body/ul/li'));
    }
}
