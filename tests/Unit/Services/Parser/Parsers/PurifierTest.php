<?php

namespace Tests\Unit\Services\Parser\Parsers;

use Coyote\Services\Parser\Parsers\Purifier;
use Tests\TestCase;

class PurifierTest extends TestCase
{
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

    public function testAllowKbd()
    {
        $parser = new Purifier();

        $input = '<kbd>Ctrl</kbd>';
        $this->assertEquals($input, $parser->parse($input));
    }

    public function testAllowMark()
    {
        $parser = new Purifier();

        $input = '<mark>Ctrl</mark>';
        $this->assertEquals($input, $parser->parse($input));
    }
}
