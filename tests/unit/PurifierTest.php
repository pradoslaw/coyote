<?php

use Coyote\Services\Parser\Parsers\Purifier;

class PurifierTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testParseLinks()
    {
        $parser = new Purifier();

        $input = '<a href="http://www.wp.pl">http://www.wp.pl</a>';
        $this->tester->assertEquals($input, $parser->parse($input));
    }

    public function testParseBlockquotes()
    {
        $parser = new Purifier();

        $input = '<blockquote>lorem ipsum<blockquote>lorem ipsum</blockquote></blockquote>';
        $this->tester->assertEquals($input, $parser->parse($input));
    }

    public function testParseUnderscore()
    {
        $parser = new Purifier();

        $input = '<u>foo</u>';
        $this->tester->assertEquals($input, $parser->parse($input));
    }
}
