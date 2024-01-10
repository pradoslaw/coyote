<?php

namespace Tests\Legacy\Services\Invoice;

use Coyote\Country;
use Coyote\Services\Invoice\VatRateCalculator;
use Tests\Legacy\TestCase;

class VatRateCalculatorTest extends TestCase
{
    public function testCalculateVatRateForPoland()
    {
        $country = Country::where('name', 'Poland')->first();
        $calculator = new VatRateCalculator();

        $this->assertEquals(1.23, $calculator->vatRate(null, null));
        $this->assertEquals(1.23, $calculator->vatRate($country, null));
        $this->assertEquals(1.23, $calculator->vatRate($country, ''));
        $this->assertEquals(1.23, $calculator->vatRate($country, '894-313-94-36'));
        $this->assertEquals(1.23, $calculator->vatRate(null, '894-313-94-36'));
    }

    public function testCalculateVatRateForAustria()
    {
        $country = Country::where('name', 'Austria')->first();
        $calculator = new VatRateCalculator();

        $this->assertEquals(1.23, $calculator->vatRate($country, null));
        $this->assertEquals(1.23, $calculator->vatRate($country, ''));
        $this->assertEquals(1, $calculator->vatRate($country, '894-313-94-36'));
        $this->assertEquals(1.23, $calculator->vatRate(null, '894-313-94-36'));
    }
}
