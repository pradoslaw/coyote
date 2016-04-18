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
        $hash = new \Coyote\Services\Parser\Reference\Hash();
        $tags = $hash->grab('<a href="">#słoma</a>');

        $this->assertEquals('słoma', $tags[0]);

        $tags = $hash->grab('<a href="">słoma</a>');
        $this->assertEquals(0, count($tags));
    }
    
    public function testGrabCityName()
    {
        $city = new \Coyote\Services\Parser\Reference\City();
        
        $cities = $city->grab('Wrocław, Warszawa');        
        $this->assertEquals(2, count($cities));
        $this->assertEquals('Wrocław', $cities[0]);
        $this->assertEquals('Warszawa', $cities[1]);

        $cities = $city->grab('Wrocław');
        $this->assertEquals('Wrocław', $cities[0]);

        $cities = $city->grab('Wrocław,Wrocław');
        $this->assertEquals(1, count($cities));

        $cities = $city->grab('Wrocław/ Warszawa');
        $this->assertEquals(2, count($cities));
        $this->assertEquals('Wrocław', $cities[0]);
        $this->assertEquals('Warszawa', $cities[1]);
    }
}