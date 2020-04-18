<?php

namespace Tests\Feature;

use Tests\TestCase;

class CensoreParserTest extends TestCase
{
    /**
     * @var \Coyote\Services\Parser\Parsers\Censore
     */
    protected $parser;

    protected function setUp()
    {
        parent::setUp();

        $word = new \Coyote\Repositories\Eloquent\WordRepository(app());
        $this->parser = new \Coyote\Services\Parser\Parsers\Censore($word);
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
