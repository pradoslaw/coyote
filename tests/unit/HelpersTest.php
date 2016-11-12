<?php

class HelpersTest extends \Codeception\TestCase\Test
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
    public function testExcerpt()
    {
        $this->assertEquals('Praca w oparciu o "kontrakt" (czyli umowa B2B)', excerpt('Praca w oparciu o "kontrakt" (czyli umowa B2B)'));
        $this->assertEquals('<xxx>', excerpt('&lt;xxx&gt;'));
    }

    public function testPlain()
    {
        $this->assertEquals('"test"', plain('<b>"test"</b>'));
        $this->assertEquals("'test'", plain("<b>'test'</b>"));
        $this->assertEquals("a > b", plain("a > b"));
        $this->assertEquals('<xxx>', plain('&lt;xxx&gt;'));
        $this->assertEquals('&lt;xxx&gt;', plain('&amp;lt;xxx&amp;gt;'));
    }
}
