<?php

namespace Tests\Feature\Services\Parser\Parsers;

use Coyote\Services\Parser\Parsers\Purifier;
use Tests\TestCase;

class PurifierTest extends TestCase
{
    // tests
    public function testParseLinks()
    {
        $parser = new Purifier();

        $input = '<a href="http://www.wp.pl">http://www.wp.pl</a>';
        $this->assertEquals($input, $parser->parse($input));
    }

    public function testParseBlockquotes()
    {
        $parser = new Purifier();

        $input = '<blockquote>lorem ipsum<blockquote>lorem ipsum</blockquote></blockquote>';
        $this->assertEquals($input, $parser->parse($input));
    }

    public function testParseUnderscore()
    {
        $parser = new Purifier();

        $input = '<u>foo</u>';
        $this->assertEquals($input, $parser->parse($input));
    }
}
