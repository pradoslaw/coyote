<?php
namespace Neon\Test\BaseFixture\Selector;

use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\TestCase;
use function Neon\Test\BaseFixture\Caught\caught;

class SelectorTest extends TestCase
{
    /**
     * @test
     */
    public function absolutePath(): void
    {
        $content = $this->find(
            new Selector('html', 'body', 'div'),
            '<div>Foo</div> <div><div>Bar</div></div>');
        $this->assertSame(['Foo'], $content);
    }

    /**
     * @test
     */
    public function noRelative(): void
    {
        $this->assertNotFound(
            new Selector('body', 'div'),
            '<div>Cat</div>');
    }

    /**
     * @test
     */
    public function blank(): void
    {
        $exception = caught(fn() => new Selector('html', ' ', 'body'));
        $this->assertSame(
            'Failed to accept empty string selector.',
            $exception->getMessage());
    }

    /**
     * @test
     */
    public function cssClass(): void
    {
        $content = $this->find(
            new Selector('html', 'body', '.foo'),
            '<div class="foo">Valar</div>
            <p class="foo">Morghulis</p>');
        $this->assertSame(['Valar', 'Morghulis'], $content);
    }

    /**
     * @test
     */
    public function cssElementClass(): void
    {
        $content = $this->find(
            new Selector('html', 'body', 'div.foo'),
            '<div class="foo">Match</div>
            <p class="foo">Other</p>');
        $this->assertSame(['Match'], $content);
    }

    /**
     * @test
     */
    public function cssClassMany(): void
    {
        $content = $this->find(
            new Selector('html', 'body', '.two'),
            '<div class="one two three">Match</div>');
        $this->assertSame(['Match'], $content);
    }

    /**
     * @test
     */
    public function cssClassSubstringStart(): void
    {
        $this->assertNotFound(
            new Selector('html', 'body', '.car'),
            '<div class="carpet">Text</div>');
    }

    /**
     * @test
     */
    public function cssClassSubstringEnd(): void
    {
        $this->assertNotFound(
            new Selector('html', 'body', '.pet'),
            '<div class="carpet">Text</div>');
    }

    /**
     * @test
     */
    public function cssClassNewline(): void
    {
        $content = $this->find(
            new Selector('html', 'body', '.foo'),
            "<div class='\nfoo\n'>Match</div>");
        $this->assertSame(['Match'], $content);
    }

    private function assertNotFound(Selector $selector, string $html): void
    {
        $this->assertSame([], $this->find($selector, $html));
    }

    private function find(Selector $selector, string $html): array
    {
        $dom = new ViewDom($html);
        return $dom->findMany($selector->xPath());
    }
}
