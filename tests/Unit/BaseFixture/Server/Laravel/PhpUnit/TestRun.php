<?php
namespace Tests\Unit\BaseFixture\Server\Laravel\PhpUnit;

use Illuminate\Foundation\Application;
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
            return new class ($this) extends Laravel\TestCase {
                use DatabaseTransactions;
            };
        }
        return new Laravel\TestCase($this);
    }

    public function beforeBoot(Application $app): void
    {
        /**
         * In order to polymorphically call {@see Test::beforeBoot},
         * there would have to be a feature in PhpUnit which allows
         * to call a "register" method before setting up laravel
         * application.
         *
         * This is a workaround, but at least when we override this method,
         * PHP type system will check it, since traits can't have duplicated
         * methods.
         */
        if (\method_exists($this->test, 'beforeBoot')) {
            $this->test->beforeBoot($app);
        }
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
