<?php
namespace Xenon\Test\Unit\ViewItem\ForEach;

use PHPUnit\Framework\TestCase;
use Xenon\ForEach_;
use Xenon\Tag;
use Xenon\Test\Unit\Fixture;
use Xenon\Text;
use Xenon\Xenon;

class IterationTest extends TestCase
{
    use Fixture;

    private Xenon $xenon;

    /**
     * @before
     */
    public function iteration(): void
    {
        $this->xenon = new Xenon([
            new ForEach_('values', [
                new Tag('b', [new Text('foo')]),
                new Text('bar'),
            ])],
            ['values' => [null, null]]);
    }

    /**
     * @test
     */
    public function ssr(): void
    {
        $this->assertHtml($this->xenon, '<b>foo</b>bar<b>foo</b>bar');
    }

    /**
     * @test
     */
    public function spa(): void
    {
        $this->assertHtmlRuntime($this->xenon, '<b>foo</b>bar<b>foo</b>bar');
    }
}
