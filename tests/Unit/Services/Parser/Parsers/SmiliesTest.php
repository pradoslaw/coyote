<?php
namespace Tests\Unit\Services\Parser\Parsers;

use Coyote\Services\Parser\Parsers\Smilies;

class SmiliesTest extends \Tests\TestCase
{
    /**
     * @test
     */
    public function test()
    {
        $parser = new Smilies();
        $this->assertSame('<img class="img-smile" alt=":)" title=":)" src="/img/smilies/smile.gif">', $parser->parse(':)'));
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
        $this->assertSame('<p><img class="img-smile" alt=":)" title=":)" src="/img/smilies/smile.gif"></p>', $parser->parse('<p>:)</p>'));
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
