<?php

namespace Tests\Unit\Services\Parser\Factories;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Coyote\Services\Parser\Factories\SigFactory;

class SigFactoryTest extends TestCase
{
    use WithFaker;

    public function testParseSignatureWithLineBreakings()
    {
        $input = "one\ntwo";

        $parser = new SigFactory($this->app);

        $this->assertEquals("one<br />\ntwo", trim($parser->parse($input)));
    }
}
