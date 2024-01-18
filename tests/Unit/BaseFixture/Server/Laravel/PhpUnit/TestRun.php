<?php
namespace Tests\Unit\BaseFixture\Server\Laravel\PhpUnit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Test;
use Tests\Unit\BaseFixture\Server\Laravel;

class TestRun
{
    private ?Laravel\TestCase $laravel = null;

    public function __construct(private Test $test)
    {
        if ($this->isLaravelApplication()) {
            $this->laravel = $this->instance();
            /** @var Laravel\Application $test */
            $test->laravel = $this->laravel;
        }
    }

    private function instance(): Laravel\TestCase
    {
        if ($this->isTransactional()) {
            return new class extends Laravel\TestCase {
                use DatabaseTransactions;
            };
        }
        return new Laravel\TestCase();
    }

    private function isLaravelApplication(): bool
    {
        return $this->hasMarker(Laravel\Application::class);
    }

    private function isTransactional(): bool
    {
        return $this->hasMarker(Laravel\Transactional::class);
    }

    private function hasMarker(string $markerClass): bool
    {
        return \in_array($markerClass, \class_uses_recursive($this->test));
    }

    public function setUp(): void
    {
        $this->laravel?->setUp();
    }

    public function tearDown(): void
    {
        $this->laravel?->tearDown();
    }
}
