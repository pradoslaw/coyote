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
    public function immediateChild(): void
    {
        $content = $this->find(
            new Selector('html', 'body', 'div'),
            '<div>Foo</div>');
        $this->assertSame(['Foo'], $content);
    }

    /**
     * @test
     */
    public function nestedChild(): void
    {
        $content = $this->find(
            new Selector('html', 'body', 'div'),
            '<div><div>Bar</div></div>');
        $this->assertSame(['Bar'], $content);
    }

    /**
     * @test
     */
    public function leafText(): void
    {
        $content = $this->find(
            new Selector('div'),
            '<div>Banana<span>Watermelon</span></div>');
        $this->assertSame(['Banana'], $content);
    }

    /**
     * @test
     */
    public function leafAttribute(): void
    {
        $content = $this->find(
            new Selector('div', '@id'),
            '<div id="apple"><span id="pear"></span></div>');
        $this->assertSame(['apple'], $content);
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
