<?php
namespace Tests\Unit\BaseFixture\Server\Laravel\PhpUnit;

use PHPUnit\Framework\Test;

class Listener extends Adapter
{
    private ?TestRun $run = null;

    public function startTest(Test $test): void
    {
        $this->run = new TestRun($test);
        $this->run->setUp();
    }

    public function endTest(Test $test, float $time): void
    {
        $this->run?->tearDown();
        $this->run = null;
    }
}
