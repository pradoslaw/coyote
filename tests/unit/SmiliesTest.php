<?php

use Coyote\Services\Parser\Parsers\Smilies;

class SmiliesTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testParseSmilies()
    {
        $parser = new Smilies;

        $this->tester->assertRegExp('/<img class="img-smile" alt="\:\)" title="\:\)" src=".*">/', $parser->parse(':)'));
        $this->tester->assertRegExp('/<p><img class="img-smile" alt="\:\)" title="\:\)" src=".*"><\/p>/', $parser->parse('<p>:)</p>'));
        $this->tester->assertRegExp('/\(\:\)\)/', $parser->parse('(:))'));
        $this->tester->assertEquals('admin:)', $parser->parse('admin:)'));
    }
}
