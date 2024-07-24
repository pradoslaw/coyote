<?php
namespace Tests\Legacy\Services\Parser\Parsers;

use Coyote\Services\Parser\Parsers\Purifier;

class PurifierTest extends \Tests\Legacy\TestCase
{
    public function testParseLinks()
    {
        $this->assertHtmlTransformation(
            '<a href="http://www.wp.pl">http://www.wp.pl</a>',
            '<a href="http://www.wp.pl" rel="nofollow">http://www.wp.pl</a>');
    }

    public function testParseBlockquotes()
    {
        $this->assertIdentity('<blockquote>lorem ipsum<blockquote>lorem ipsum</blockquote></blockquote>');
    }

    public function testParseUnderscore()
    {
        $this->assertIdentity('<u>foo</u>');
    }

    public function testAllowKbd()
    {
        $this->assertIdentity('<kbd>Ctrl</kbd>');
    }

    public function testAllowMark()
    {
        $this->assertIdentity('<mark>Ctrl</mark>');
    }

    public function testAllowIframeInsideSpan()
    {
        $this->assertIdentity('<p><span class="embed-responsive embed-responsive-16by9"><iframe src="https://youtube.com/embed/enOjqwOE1ec" class="embed-responsive-item"></iframe></span></p>');
    }

    private function assertIdentity(string $input): void
    {
        $this->assertHtmlTransformation($input, $input);
    }

    private function assertHtmlTransformation(string $input, $expected): void
    {
        $this->assertEquals($expected, (new Purifier())->parse($input));
    }
}
