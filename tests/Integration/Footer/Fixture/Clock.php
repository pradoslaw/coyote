<?php
namespace Tests\Integration\Footer\Fixture;

use Coyote\Domain;
use Illuminate\Foundation\Application;
use Tests\Integration\BaseFixture\Server\Laravel;

trait Clock
{
    use Laravel\Application;

    var ?FixedClock $clock = null;

    function beforeBoot(Application $app): void
    {
        $this->clock = new FixedClock();
        $app->instance(Domain\Clock::class, $this->clock);
    }

    function systemYear(int $year): void
    {
        $this->clock->setYear($year);
    }

    function fixedExecutionTime(float $seconds): void
    {
        $this->clock->setExecutionTime($seconds);
    }
}
