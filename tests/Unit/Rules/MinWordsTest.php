<?php

namespace Tests\Unit\Rules;

use Coyote\Rules\MinWords;
use Coyote\User;
use Illuminate\Support\Facades\Auth;
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

        $user = factory(User::class)->make(['reputation' => 999]);

        Auth::setUser($user);

        $this->assertFalse($rule->passes('', 'test test'));
        $this->assertFalse($rule->passes('', 'test i test'));
        $this->assertTrue($rule->passes('', 'to jest test'));
    }

    public function testShortTextShouldPass()
    {
        $rule = new MinWords();
        $user = factory(User::class)->make(['reputation' => 1000]);

        Auth::setUser($user);

        $this->assertTrue($rule->passes('', 'test'));
        $this->assertTrue($rule->passes('', 'test test'));
        $this->assertTrue($rule->passes('', 'test i test'));
    }
}
