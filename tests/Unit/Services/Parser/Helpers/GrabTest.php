<?php

namespace Tests\Unit\Services\Parser\Helpers;

use Tests\TestCase;

class GrabTest extends TestCase
{
    public function testGrabCityName()
    {
        $city = new \Coyote\Services\Parser\Helpers\City();

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

    public function testTryToGrabLoginAndReturnNoError()
    {
        $helper = new \Coyote\Services\Parser\Helpers\Login();

        $input = '"test" A < B <b>test</b> <bald>';
        $this->assertEquals([], $helper->grab($input));
    }
}
