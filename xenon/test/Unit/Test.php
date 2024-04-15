<?php
namespace Xenon\Test\Unit;

use PHPUnit\Framework\TestCase;
use Xenon\Tag;
use Xenon\TagField;
use Xenon\Xenon;

class Test extends TestCase
{
    use Fixture;

    // goal:
    // create ssr and spa, that can be used both in client and server based 
    // on one input model

    // solution:
    // - generate ssr page
    // - generate vue, that matches that ssr structure
    // - based on common input state
    // - based on common skeleton that maps state to structure

    // todo zagnieżdżone elementy (obiekty i listy)
    // todo formularze
    // todo websocket
    // todo ajax requests, view callbacks
    // todo handle runtime js errors
    // todo centralized horizontal stuff

    /**
     * @test
     */
    public function ssr(): void
    {
        $xenon = new Xenon([
            new Tag('div', [
                new TagField('p', 'title'),
                new TagField('span', 'text')])],
            ['title' => 'foo', 'text' => 'bar']);
        $this->assertHtml($xenon, '<body><div><p>foo</p><span>bar</span></div></body>');
    }
}
