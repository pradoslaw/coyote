<?php

namespace Tests\Unit\Services\Parser\Parsers;

use Tests\TestCase;

class CensoreTest extends TestCase
{
    /**
     * @var \Coyote\Services\Parser\Parsers\Censore
     */
    protected $parser;

    protected function setUp(): void
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
