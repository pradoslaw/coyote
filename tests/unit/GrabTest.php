<?php

class GrabTest extends \Codeception\TestCase\Test
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
    public function testParseUserName()
    {
        $hash = new \Coyote\Parser\Reference\Hash();
        $tags = $hash->grab('<a href="">#słoma</a>');

        $this->assertEquals('słoma', $tags[0]);

        $tags = $hash->grab('<a href="">słoma</a>');
        $this->assertEquals(0, count($tags));
    }
}