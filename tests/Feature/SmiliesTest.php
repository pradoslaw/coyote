<?php

namespace Tests\Feature;

use Coyote\Services\Parser\Parsers\Smilies;
use Tests\TestCase;

class SmiliesTest extends TestCase
{
    // tests
    public function testParseSmilies()
    {
        $parser = new Smilies();

        $this->assertRegExp('/<img class="img-smile" alt="\:\)" title="\:\)" src=\"\/img\/smilies\/smile\.gif\">/', $parser->parse(':)'));
        $this->assertRegExp('/<p><img class="img-smile" alt="\:\)" title="\:\)" src="\/img\/smilies\/smile.gif"><\/p>/', $parser->parse('<p>:)</p>'));
        $this->assertRegExp('/\(\:\)\)/', $parser->parse('(:))'));
        $this->assertEquals('admin:)', $parser->parse('admin:)'));
    }
}
