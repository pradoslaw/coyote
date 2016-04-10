<?php

class CityValidatorTest extends \Codeception\TestCase\Test
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
    public function testValidateCityName()
    {
        $validator = new \Coyote\CityValidator();

        $this->assertTrue($validator->validateTag([], 'Wrocław', [], null));
        $this->assertTrue($validator->validateTag([], 'Wrocław, Warszawa', [], null));
        $this->assertTrue($validator->validateTag([], 'Wrocław/Warszawa', [], null));
        $this->assertTrue($validator->validateTag([], 'Zielona góra/Warszawa', [], null));
        $this->assertFalse($validator->validateTag([], 'Warszawa (ścisłe centrum)', [], null));
        $this->assertTrue($validator->validateTag([], 'Warszawa', [], null));
        $this->assertTrue($validator->validateTag([], 'Kraków', [], null));
        $this->assertTrue($validator->validateTag([], 'Gdańsk', [], null));
        $this->assertTrue($validator->validateTag([], 'Łódź', [], null));
        $this->assertTrue($validator->validateTag([], 'Garðabær', [], null));
        $this->assertTrue($validator->validateTag([], 'Sauðárkrókur', [], null));
        $this->assertTrue($validator->validateTag([], 'Provence-Alpes-Côte d\'Azur', [], null));
    }
}