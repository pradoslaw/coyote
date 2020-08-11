<?php

namespace Tests\Feature\Services\Helpers;

use Carbon\Carbon;

trait TestCarbonLocale
{
    /** @var string */
    private $locale;

    protected function setUp()
    {
        $this->locale = Carbon::getLocale();
        Carbon::setLocale('pl');
    }

    protected function tearDown()
    {
        Carbon::setLocale($this->locale);
    }
}
