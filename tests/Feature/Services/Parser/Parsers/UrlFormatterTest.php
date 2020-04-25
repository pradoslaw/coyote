<?php

namespace Tests\Feature\Services\Parser\Parsers;

use Collective\Html\HtmlBuilder;
use Coyote\Services\Parser\Parsers\UrlFormatter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UrlFormatterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldParseLink()
    {
        // given
        $formatter = new UrlFormatter('', $this->html('http://4pr.net/Forum'));

        // when
        $result = $formatter->parse('text 4pr.net/Forum text');

        // then
        $this->assertEquals('text <a>4pr.net/Forum</a> text', $result);
    }

    /**
     * @test
     */
    public function shouldTruncateLongLink()
    {
        // given
        $longUrl = 'https://scrutinizer-ci.com/g/adam-boduch/coyote/inspections/8778b728-ef73-4167-8092-424a57a8e66d';
        $formatter = new UrlFormatter('', $this->html($longUrl));

        // when
        $result = $formatter->parse("link: $longUrl");

        // then
        $this->assertEquals('link: <a>https://scrutinizer-ci.com/g/[...]8-ef73-4167-8092-424a57a8e66d</a>', $result);
    }

    /**
     * @test
     */
    public function shouldNotTruncateLongLink_hostLink()
    {
        // given
        $longUrl = 'https://4pr.net/g/adam-boduch/coyote/inspections/8778b728-ef73-4167-8092-424a57a8e66d';
        $formatter = new UrlFormatter('4pr.net', $this->html($longUrl));

        // when
        $result = $formatter->parse("link: $longUrl");

        // then
        $this->assertEquals('link: <a>https://4pr.net/g/adam-boduch/coyote/inspections/8778b728-ef73-4167-8092-424a57a8e66d</a>', $result);
    }

    /**
     * @test
     */
    public function shouldIncludeParenthesis()
    {
        // given
        $formatter = new UrlFormatter('', $this->html('http://4pr.net/Forum/(text)'));

        // when
        $result = $formatter->parse('text 4pr.net/Forum/(text) text');

        // then
        $this->assertEquals('text <a>4pr.net/Forum/(text)</a> text', $result);
    }

    /**
     * @test
     */
    public function shouldIncludeNestedParenthesis()
    {
        // given
        $formatter = new UrlFormatter('', $this->html('http://4pr.net/Forum/(t(ex)t)'));

        // when
        $result = $formatter->parse('text 4pr.net/Forum/(t(ex)t) text');

        // then
        $this->assertEquals('text <a>4pr.net/Forum/(t(ex)t)</a> text', $result);
    }

    /**
     * @test
     */
    public function shouldNotIncludeUnmatchedParenthesis()
    {
        // given
        $formatter = new UrlFormatter('', $this->html('http://4pr.net/Forum/(text)'));

        // when
        $result = $formatter->parse('text 4pr.net/Forum/(text)( text');

        // then
        $this->assertEquals('text <a>4pr.net/Forum/(text)</a>( text', $result);
    }

    /**
     * @test
     */
    public function shouldNotIncludeUnmatchedNestedParenthesis()
    {
        // given
        $formatter = new UrlFormatter('', $this->html('http://4pr.net/Forum/'));

        // when
        $result = $formatter->parse('4pr.net/Forum/(tex(t)');

        // then
        $this->assertEquals('<a>4pr.net/Forum/</a>(tex(t)', $result);
    }

    /**
     * @test
     */
    public function shouldHandleCatastrophicBacktracking_withUnmatchedParenthesis()
    {
        // given
        $errorProneLink = 'http://4pr.net/Forum/(long_long_long_long_long';
        $formatter = new UrlFormatter('', $this->html('http://4pr.net/Forum/'));

        // when
        $result = $formatter->parse($errorProneLink);

        // then
        $this->assertEquals('<a>http://4pr.net/Forum/</a>(long_long_long_long_long', $result);
    }

    private function html(string $expectedHref = null): HtmlBuilder
    {
        /** @var HtmlBuilder|MockObject $html */
        $html = $this->createMock(HtmlBuilder::class);
        $html
            ->expects($this->once())
            ->method('link')
            ->will($this->returnCallback(function (string $href, string $title) use ($expectedHref): string {
                if ($expectedHref) {
                    $this->assertEquals($expectedHref, $href, 'Failed asserting that parsed link contains expected href attribute');
                }
                return "<a>$title</a>";
            }));
        return $html;
    }
}
