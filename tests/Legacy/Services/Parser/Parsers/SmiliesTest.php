<?php
namespace Tests\Legacy\Services\Parser\Parsers;

use Coyote\Services\Parser\Parsers\Smilies;

class SmiliesTest extends \Tests\Legacy\TestCase
{
    /**
     * @test
     */
    public function test()
    {
        $parser = new Smilies();
        $this->assertSame("<img class='img-smile' alt='😀' title='Smiling Face' src='https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/svg/1f600.svg'>", $parser->parse(':)'));
    }

    /**
     * @test
     */
    public function smileInParenthesis()
    {
        $this->assertIdentity(new Smilies(), '(:))');
    }

    /**
     * @test
     */
    public function smileInParagraph()
    {
        $parser = new Smilies();
        $this->assertSame("<p><img class='img-smile' alt='😀' title='Smiling Face' src='https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/svg/1f600.svg'></p>", $parser->parse('<p>:)</p>'));
    }

    /**
     * @test
     */
    public function smileAfterWord()
    {
        $this->assertIdentity(new Smilies(), 'admin:)');
    }

    private function assertIdentity(Smilies $parser, string $text): void
    {
        $this->assertEquals($text, $parser->parse($text));
    }
}
