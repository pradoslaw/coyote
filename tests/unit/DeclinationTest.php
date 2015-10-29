<?php

require __DIR__ . '/../../app/Libs/Declination/Declination.php';

class DeclinationTest extends \Codeception\TestCase\Test
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

    private function getSeconds($value)
    {
        return Declination\Declination::format($value, ['sekunda', 'sekundy', 'sekund']);
    }

    // tests
    public function testIfDeclinationReturnsCorrectFormatOfSeconds()
    {
        $this->assertSame('1 sekunda', $this->getSeconds(1));
        $this->assertSame('2 sekundy', $this->getSeconds(2));
        $this->assertSame('3 sekundy', $this->getSeconds(3));
        $this->assertSame('10 sekund', $this->getSeconds(10));
        $this->assertSame('11 sekund', $this->getSeconds(11));
        $this->assertSame('99 sekund', $this->getSeconds(99));
        $this->assertSame('100 sekund', $this->getSeconds(100));
        $this->assertSame('0 sekund', $this->getSeconds(0));
    }
}