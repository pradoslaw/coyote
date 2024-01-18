<?php
namespace Tests\Unit\BaseFixture\Server\Laravel\PhpUnit;

use PHPUnit\Framework\Test;

class Listener extends Adapter
{
    public function startTest(Test $test): void
    {
    }

    public function endTest(Test $test, float $time): void
    {
    }
}
