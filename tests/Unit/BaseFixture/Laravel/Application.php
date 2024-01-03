<?php
namespace Tests\Unit\BaseFixture\Laravel;

use Tests\Unit\BaseFixture\Laravel;

trait Application
{
    var ?Laravel\TestCase $laravel = null;

    /**
     * @before
     */
    function initializeApplication(): void
    {
        $this->laravel = new Laravel\TestCase();
        $this->laravel->setUp();
    }

    /**
     * @after
     */
    function finalizeApplication(): void
    {
        $this->laravel->tearDown();
    }
}
