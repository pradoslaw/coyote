<?php

use Coyote\Services\Parser\Parsers\Autolink;

class AutolinkTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testParseLinks()
    {
        $parser = new Autolink();

        $input = '@4programmers.net';
        $this->tester->assertEquals($input, $parser->parse($input));

        $input = '<a href="http://4programmers.net">4programmers</a>.net';
        $this->tester->assertEquals($input, $parser->parse($input));

        $input = 'www.4programmers.net';
        $this->tester->assertEquals('<a href="http://www.4programmers.net">www.4programmers.net</a>', $parser->parse($input));
    }
}
