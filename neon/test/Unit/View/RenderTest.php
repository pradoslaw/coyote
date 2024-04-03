<?php
namespace Neon\Test\Unit\View;

use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View\Html;
use Neon\View\Html\Render;
use Neon\View\Html\UntypedItem;
use Neon\View\HtmlView;
use PHPUnit\Framework\TestCase;

class RenderTest extends TestCase
{
    /**
     * @test
     */
    public function childClass(): void
    {
        $h = new Render();
        $parent = $h('div', [$this->child($h, 'foo')], []);
        $this->assertClass('foo', $parent);
    }

    /**
     * @test
     */
    public function joinedClass(): void
    {
        $h = new Render();
        $child = $this->child($h, 'foo cat');
        $parent = $h('div', [$child], ['class' => 'bar dor']);
        $this->assertClass('bar dor foo cat', $parent);
    }

    /**
     * @test
     */
    public function secondChild(): void
    {
        $h = new Render();
        $child = $this->child($h, 'bar');
        $parent = $h('div', ['first', $child],[]);
        $this->assertClass('bar', $parent);
    }

    /**
     * @test
     */
    public function childrenDuplicateClass(): void
    {
        $h = new Render();
        $first = $this->child($h, 'bar');
        $second = $this->child($h, 'bar');
        $parent = $h('div', [$first, $second],[]);
        $this->assertClass('bar', $parent);
    }

    /**
     * @test
     */
    public function childrenDifferentClass(): void
    {
        $h = new Render();
        $first = $this->child($h, 'foo');
        $second = $this->child($h, 'bar');
        $parent = $h('div', [$first, $second],[]);
        $this->assertClass('foo bar', $parent);
    }

    private function assertClass(string $expectedClass, Html\Tag $tag): void
    {
        $view = new HtmlView([], [
            new UntypedItem(fn(Render $h): array => [$tag]),
        ]);
        $dom = new ViewDom($view->html());
        $this->assertSame(
            $expectedClass,
            $dom->find('//div/@class'));
    }

    private function child(Render $h, string $parentClass): Html\Tag
    {
        return $h('span', [], ['parentClass' => $parentClass]);
    }
}
