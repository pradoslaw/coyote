<?php

use Coyote\User;

class MacroTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testExceptUsersMacro()
    {
        $users = collect([(new User)->forceFill(['id' => 1]), (new User)->forceFill(['id' => 2]), (new User)->forceFill(['id' => 3])]);

        $result = $users->exceptUsers([(new User)->forceFill(['id' => 1])]);

        $this->tester->assertEquals(2, count($result));
        $this->tester->assertEquals(2, $result->first()->id);
        $this->tester->assertEquals(3, $result->last()->id);

        ////////////////////////////////////////////////////////////////////

        $users = collect([(new User)->forceFill(['id' => 1])]);

        $result = $users->exceptUsers([(new User)->forceFill(['id' => 1]), (new User)->forceFill(['id' => 2]), (new User)->forceFill(['id' => 3])]);

        $this->tester->assertEquals(0, count($result));
    }
}
