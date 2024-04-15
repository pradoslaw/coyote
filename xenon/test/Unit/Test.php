<?php
namespace Xenon\Test\Unit;

use PHPUnit\Framework\TestCase;
use Xenon\Tag;
use Xenon\TagField;
use Xenon\Xenon;

class Test extends TestCase
{
    use Fixture;

    /**
     * @test
     */
    public function ssrHtmlView(): void
    {
        $xenon = new Xenon([
            new Tag('div', [
                new TagField('p', 'title'),
                new TagField('span', 'text')])],
            ['title' => 'foo', 'text' => 'bar']);
        $this->assertHtml($xenon, '<body><div><p>foo</p><span>bar</span></div></body>');
    }

    /**
     * @test
     */
    public function spaReactiveState(): void
    {
        $xenon = new Xenon(
            [new TagField('i', 'favouriteColour')],
            ['favouriteColour' => 'red'],
        );
        $this->assertHtmlRuntime(
            $xenon,
            "xenon.setState('favouriteColour', 'green');",
            '<body><i>green</i></body>');
    }
}
