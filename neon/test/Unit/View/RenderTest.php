<?php
namespace Neon\Test\Unit\View;

use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View\Html;
use Neon\View\Html\Render;
use PHPUnit\Framework\TestCase;

class RenderTest extends TestCase
{
    /**
     * @test
     */
    public function childClass(): void
    {
        $h = new Render();
        $parent = $h->tag('div', [], [$this->child($h, 'foo')]);
        $this->assertClass('foo', $parent);
    }

    /**
     * @test
     */
    public function joinedClass(): void
    {
        $h = new Render();
        $child = $this->child($h, 'foo cat');
        $parent = $h->tag('div', ['class' => 'bar dor'], [$child]);
        $this->assertClass('bar dor foo cat', $parent);
    }

    /**
     * @test
     */
    public function secondChild(): void
    {
        $h = new Render();
        $child = $this->child($h, 'bar');
        $parent = $h->tag('div', [], ['first', $child]);
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
        $parent = $h->tag('div', [], [$first, $second]);
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
        $parent = $h->tag('div', [], [$first, $second]);
        $this->assertClass('foo bar', $parent);
    }

    private function assertClass(string $expectedClass, Html\Tag $tag): void
    {
        $dom = new ViewDom($tag->html());
        $this->assertSame(
            $expectedClass,
            $dom->findText('//div/@class'));
    }

    private function child(Render $h, string $parentClass): Html\Tag
    {
        return $h->tag('span', ['parentClass' => $parentClass], []);
    }
}
