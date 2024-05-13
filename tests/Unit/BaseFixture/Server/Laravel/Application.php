<?php
namespace Tests\Unit\BaseFixture\Server\Laravel;

use Tests\Unit\BaseFixture\Server\Laravel;

trait Application
{
    var ?Laravel\TestCase $laravel = null;

    /**
     * @before
     */
    function initializeLaravel(): void
    {
        $this->laravel = StaticLaravel::get($this);
    }

    /**
     * @after
     */
    function finalizeLaravel(): void
    {
        StaticLaravel::destroy();
    }
}
