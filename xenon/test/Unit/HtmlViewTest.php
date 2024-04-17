<?php
namespace Xenon\Test\Unit;

use PHPUnit\Framework\TestCase;
use Xenon\Tag;
use Xenon\TagField;
use Xenon\Xenon;

class HtmlViewTest extends TestCase
{
    use Fixture;

    private Xenon $xenon;

    /**
     * @before
     */
    public function htmlView(): void
    {
        $this->xenon = new Xenon([
            new Tag('div', [], [], [
                new TagField('p', 'favouriteColour'),
                new TagField('p', 'leastFavouriteColour'),
            ])],
            ['favouriteColour' => 'red', 'leastFavouriteColour' => 'pink']);
    }

    /**
     * @test
     */
    public function ssr(): void
    {
        $this->assertHtml($this->xenon, '<div><p>red</p><p>pink</p></div>');
    }

    /**
     * @test
     */
    public function spa(): void
    {
        $this->assertHtmlRuntime($this->xenon, '<div><p>red</p><p>pink</p></div>');
    }

    /**
     * @test
     */
    public function spaReactiveState(): void
    {
        $xenon = new Xenon(
            [new TagField('i', 'favouriteColour')],
            ['favouriteColour' => 'red']);
        $this->executeAndAssertHtmlRuntime(
            $xenon,
            "xenon.setState('favouriteColour', 'green');",
            '<i>green</i>');
    }
}
