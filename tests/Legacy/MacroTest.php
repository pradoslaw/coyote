<?php

namespace Tests\Legacy;

use Coyote\User;
use Tests\TestCase;

class MacroTest extends TestCase
{
    // tests
    public function testExceptUsersMacro()
    {
        $users = collect([(new User)->forceFill(['id' => 1]), (new User)->forceFill(['id' => 2]), (new User)->forceFill(['id' => 3])]);

        $result = $users->exceptUsers([(new User)->forceFill(['id' => 1])]);

        $this->assertEquals(2, count($result));
        $this->assertEquals(2, $result->first()->id);
        $this->assertEquals(3, $result->last()->id);

        $users = collect([(new User)->forceFill(['id' => 1])]);

        $result = $users->exceptUsers([(new User)->forceFill(['id' => 1]), (new User)->forceFill(['id' => 2]), (new User)->forceFill(['id' => 3])]);

        $this->assertEquals(0, count($result));
    }
}
