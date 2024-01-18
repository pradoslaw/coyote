<?php
namespace Tests\Unit\BaseFixture\Server\Laravel\PhpUnit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Test;
use Tests\Unit\BaseFixture\Server\Laravel;
use Tests\Unit\BaseFixture\Server\Laravel\Transactional;

class Listener extends Adapter
{
    private ?Laravel\TestCase $laravel = null;

    public function startTest(Test $test): void
    {
        if (!\in_array(Laravel\Application::class, \class_uses_recursive($test))) {
            return;
        }
        $this->laravel = $this->instance($test);
        $this->laravel->setUp();

        /** @var Laravel\Application $test */
        $test->laravel = $this->laravel;
    }

    public function endTest(Test $test, float $time): void
    {
        $this->laravel?->tearDown();
        $this->laravel = null;
    }

    private function instance(Test $test): Laravel\TestCase
    {
        if ($this->hasTransactionalMarker($test)) {
            return new class extends Laravel\TestCase {
                use DatabaseTransactions;
            };
        }
        return new Laravel\TestCase();
    }

    private function hasTransactionalMarker(Test $test): bool
    {
        return \in_array(Transactional::class, \class_uses_recursive($test));
    }
}
