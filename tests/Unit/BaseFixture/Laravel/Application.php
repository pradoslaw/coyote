<?php
namespace Tests\Unit\BaseFixture\Laravel;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Unit\BaseFixture\Laravel;

trait Application
{
    var ?Laravel\TestCase $laravel = null;

    /**
     * @before
     */
    function initializeApplication(): void
    {
        $this->laravel = $this->instance();
        $this->laravel->setUp();
    }

    /**
     * @after
     */
    function finalizeApplication(): void
    {
        $this->laravel->tearDown();
    }

    function instance(): Laravel\TestCase
    {
        if ($this->hasTransactionalMarker()) {
            return new class extends Laravel\TestCase {
                use DatabaseTransactions;
            };
        }
        return new Laravel\TestCase();
    }

    function hasTransactionalMarker(): bool
    {
        return \in_array(Transactional::class, \class_uses_recursive(static::class));
    }
}
