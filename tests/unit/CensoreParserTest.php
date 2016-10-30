<?php

use Coyote\Services\Parser\Parsers\Markdown;

class CensoreParserTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \Coyote\Services\Parser\Parsers\Censore
     */
    protected $parser;

    protected function _before()
    {
        $word = new \Coyote\Repositories\Eloquent\WordRepository(app());
        $this->parser = new \Coyote\Services\Parser\Parsers\Censore($word);
    }

    protected function _after()
    {

    }

    public function testHashCodeTag()
    {
        $text = '<code></code><code>kurczak';

        $result = $this->parser->parse($text);
        $this->assertEquals($result, $text);

        $text = '<code><code><code></code><code>kurczak';

        $result = $this->parser->parse($text);
        $this->assertEquals($result, $text);

        $text = '</code></code><code>kurczak';

        $result = $this->parser->parse($text);
        $this->assertEquals($result, $text);
    }
}
