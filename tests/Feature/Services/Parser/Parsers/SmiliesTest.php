<?php

namespace Tests\Feature\Services\Parser\Parsers;

use Coyote\Services\Parser\Parsers\Smilies;
use Tests\TestCase;

class SmiliesTest extends TestCase
{
    // tests
    public function testParseSmilies()
    {
        $parser = new Smilies();

        $this->assertMatchesRegularExpression('/<img class="img-smile" alt="\:\)" title="\:\)" src=\"\/img\/smilies\/smile\.gif\">/', $parser->parse(':)'));
        $this->assertMatchesRegularExpression('/<p><img class="img-smile" alt="\:\)" title="\:\)" src="\/img\/smilies\/smile.gif"><\/p>/', $parser->parse('<p>:)</p>'));
        $this->assertMatchesRegularExpression('/\(\:\)\)/', $parser->parse('(:))'));
        $this->assertEquals('admin:)', $parser->parse('admin:)'));
    }
}
