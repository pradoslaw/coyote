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
        $validator = new \Coyote\Http\Validators\CityValidator();

        $this->assertTrue($validator->validateCity([], 'Wrocław', [], null));
        $this->assertTrue($validator->validateCity([], 'Wrocław, Warszawa', [], null));
        $this->assertTrue($validator->validateCity([], 'Wrocław/Warszawa', [], null));
        $this->assertTrue($validator->validateCity([], 'Zielona góra/Warszawa', [], null));
        $this->assertFalse($validator->validateCity([], 'Warszawa (ścisłe centrum)', [], null));
        $this->assertTrue($validator->validateCity([], 'Warszawa', [], null));
        $this->assertTrue($validator->validateCity([], 'Kraków', [], null));
        $this->assertTrue($validator->validateCity([], 'Gdańsk', [], null));
        $this->assertTrue($validator->validateCity([], 'Łódź', [], null));
        $this->assertTrue($validator->validateCity([], 'Garðabær', [], null));
        $this->assertTrue($validator->validateCity([], 'Sauðárkrókur', [], null));
        $this->assertTrue($validator->validateCity([], 'Provence-Alpes-Côte d\'Azur', [], null));
    }
}