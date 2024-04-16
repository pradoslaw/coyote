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
        $this->assertHtml($xenon, '<div><p>foo</p><span>bar</span></div>');
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
        $this->executeAndAssertHtmlRuntime(
            $xenon,
            "xenon.setState('favouriteColour', 'green');",
            '<i>green</i>');
    }

    /**
     * @test
     */
    public function spaHtmlView(): void
    {
        $xenon = new Xenon([
            new Tag('div', [
                new TagField('p', 'favouriteColour'),
                new TagField('p', 'leastFavouriteColour'),
            ])],
            ['favouriteColour' => 'red', 'leastFavouriteColour' => 'pink'],
        );
        $this->assertHtmlRuntime($xenon, '<div><p>red</p><p>pink</p></div>');
    }
}
