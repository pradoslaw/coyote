<?php

namespace Tests\Unit\Rules;

use Coyote\Rules\MinWords;
use Tests\TestCase;

class MinWordsTest extends TestCase
{
    public function testShortTextShouldFail()
    {
        $rule = new MinWords();

        $this->assertFalse($rule->passes('', 'test'));
        $this->assertFalse($rule->passes('', 'test test'));
        $this->assertFalse($rule->passes('', 'test i test'));
        $this->assertTrue($rule->passes('', 'to jest test'));
    }
}
